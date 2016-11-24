<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Workflow_Notification;

use OCA\Workflow\PublicAPI\Event\CollectTypesInterface;
use OCA\Workflow\PublicAPI\Event\FileActionInterface;
use OCA\Workflow\PublicAPI\Event\ValidateFlowInterface;
use OCP\BackgroundJob\IJobList;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\IFactory;

class SendEmailPlugin {

	protected $triggers = [
		'createFile',
		'deleteFile',
	];

	protected $targets = [
		'owner',
	];

	/** @var IJobList */
	protected $jobList;

	/** @var IUserSession */
	protected $userSession;

	/** @var IFactory */
	protected $l10nFactory;

	/**
	 * ConverterPlugin constructor.
	 *
	 * @param IJobList $jobList
	 * @param IUserSession $userSession
	 * @param IFactory $l10nFactory
	 */
	public function __construct(IJobList $jobList, IUserSession $userSession, IFactory $l10nFactory) {
		$this->jobList = $jobList;
		$this->userSession = $userSession;
		$this->l10nFactory = $l10nFactory;
	}

	/**
	 * @param FileActionInterface $event
	 * @param string $eventTrigger
	 */
	public function fileAction(FileActionInterface $event, $eventTrigger) {
		$flow = $event->getFlow();
		if ($flow->getType() !== 'workflow_notification') {
			return;
		}

		$action = $flow->getActions();
		if ($eventTrigger !== 'OCA\Workflow\Engine::' . $action['trigger']) {
			return;
		}

		$data = [
			'path' => $event->getPath(),
			'action' => $action,
			'fileId' => null,
			'uid' => null,
		];

		try {
			$data['fileId'] = $event->getFileId();
		} catch (\BadMethodCallException $e) {
			// Ignore
		}

		$user = $this->userSession->getUser();
		if ($user instanceof IUser) {
			$data['uid'] = $user->getUID();
		}

		$this->jobList->add('OCA\Workflow_Notification\BackgroundJob', $data);
	}

	/**
	 * @param CollectTypesInterface $event
	 */
	public function collectTypes(CollectTypesInterface $event) {
		$event->addType('workflow_notification', 'Send email notification');
	}

	/**
	 * Make sure the tags exist and are unique
	 *
	 * @param ValidateFlowInterface $event
	 */
	public function listenValidateFlow(ValidateFlowInterface $event) {
		$flow = $event->getFlow();
		if ($flow->getType() !== 'workflow_notification') {
			return;
		}

		$actions = $flow->getActions();

		if (!is_array($actions) || !isset($actions['trigger']) || !in_array($actions['trigger'], $this->triggers)) {
			$l = $this->l10nFactory->get('workflow_notification');
			throw new \OutOfBoundsException((string) $l->t('No valid notification trigger given'), 3);
		}

		if (!is_array($actions) || !isset($actions['target']) || !in_array($actions['target'], $this->targets)) {
			$l = $this->l10nFactory->get('workflow_notification');
			throw new \OutOfBoundsException((string) $l->t('No valid notification target given'), 3);
		}

		if (!is_array($actions) || !isset($actions['send']) || $actions['send'] !== 'email') {
			$l = $this->l10nFactory->get('workflow_notification');
			throw new \OutOfBoundsException((string) $l->t('No valid notification send method given'), 3);
		}

		$event->setFlowActions([
			'trigger' => $actions['trigger'],
			'target' => $actions['target'],
			'send' => 'email',
		]);

		// No other plugin needs to take care of this flow.
		$event->stopPropagation();
	}
}
