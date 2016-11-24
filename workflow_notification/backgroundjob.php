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

use OC\Files\View;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;

class BackgroundJob extends \OC\BackgroundJob\QueuedJob {

	/** @var IConfig */
	protected $config;

	/** @var IUserManager */
	protected $userManager;

	/** @var IUserSession */
	protected $userSession;

	/** @var IMailer */
	protected $mailer;

	/** @var IFactory */
	protected $l10nFactory;

	/**
	 * BackgroundJob constructor.
	 *
	 * @param IConfig $config
	 * @param IUserManager $userManager
	 * @param IUserSession $userSession
	 * @param IMailer $mailer
	 * @param IFactory $l10nFactory
	 * @param ILogger $logger
	 */
	public function __construct(IConfig $config, IUserManager $userManager, IUserSession $userSession, IMailer $mailer, IFactory $l10nFactory, ILogger $logger) {
		$this->config = $config;
		$this->userManager = $userManager;
		$this->userSession = $userSession;
		$this->mailer = $mailer;
		$this->l10nFactory = $l10nFactory;
	}

	/**
	 * @param mixed $argument
	 */
	protected function run($argument) {
		if ($argument['action']['target'] === 'owner') {
			$this->notifyOwner($argument);
		}
	}

	/**
	 * @param array $arguments
	 */
	public function notifyOwner(array $arguments) {
		$path = explode('/', $arguments['path'], 4);

		if (!isset($path[3])) {
			// Empty path
			return;
		}

		$owner = $this->userManager->get($path[1]);
		$user = $this->userManager->get($arguments['uid']);

		if (!($owner instanceof IUser)) {
			// No user
			return;
		}

		if ($arguments['uid'] === $owner->getUID()) {
			// Owner did the action, don't send an email
			return;
		}

		$userEmail = $owner->getEMailAddress();
		if (!$userEmail) {
			// User has no email, so nothing to send
			return;
		}

		$defaultLanguage = $this->config->getSystemValue('default_language', 'en');
		$userLanguage = $this->config->getUserValue($owner->getUID(), 'core', 'lang', $defaultLanguage);
		$l = $this->l10nFactory->get('workflow_notification', $userLanguage);

		switch ($arguments['action']['trigger']) {
			case 'createFile':
				$this->setUpUserFilesystem($owner->getUID());
				$view = $this->createView($owner->getUID());

				if (!$view->file_exists($path[3])) {
					// File does not exist anymore
					return;
				}

				$subject = $l->t('File created');
				$message = $l->t('File "%1$s" has been uploaded by %2$s', [
					$path[3],
					$user->getDisplayName(),
				]);
			break;

			case 'deleteFile':
				$subject = $l->t('File deleted');
				$message = $l->t('File "%1$s" has been deleted by %2$s', [
					$path[3],
					$user->getDisplayName(),
				]);
			break;

			default:
				// Wrong trigger
				return;
		}

		$mail = $this->mailer->createMessage();
		$mail->setSubject($subject);
		$mail->setTo([$owner->getEMailAddress() => $owner->getDisplayName()]);
		$mail->setPlainBody($message);
		$this->mailer->send($mail);
	}

	/**
	 * @param string $uid
	 */
	protected function setUpUserFilesystem($uid) {
		\OC\Files\Filesystem::init($uid, '/' . $uid . '/files');
	}

	/**
	 * @param string $uid
	 * @return View
	 */
	protected function createView($uid) {
		return new View('/' . $uid . '/files');
	}
}
