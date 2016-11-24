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

namespace OCA\Workflow_Notification\AppInfo;

use OCA\Workflow\PublicAPI\Event\CollectTypesInterface;
use OCA\Workflow\PublicAPI\Event\FileActionInterface;
use OCA\Workflow\PublicAPI\Event\ValidateFlowInterface;

class Application extends \OCP\AppFramework\App {

	/**
	 * Application constructor.
	 */
	public function __construct() {
		parent::__construct('workflow_notification');
	}

	/**
	 * Register the app to several events
	 */
	public function registerListeners() {
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();

		// Plugin functionality events
		$dispatcher->addListener('OCA\Workflow\Engine::createFile', [$this, 'fileActionListener']);
		$dispatcher->addListener('OCA\Workflow\Engine::deleteFile', [$this, 'fileActionListener']);

		// Workflow engine events
		$dispatcher->addListener('OCA\Workflow\Engine::validateFlow', [$this, 'validateFlowListener']);
		$dispatcher->addListener('OCA\Workflow\Engine::collectTypes', [$this, 'collectTypesListener']);
		$dispatcher->addListener('OC\Settings\Admin::loadAdditionalScripts', [$this, 'loadAdminScripts']);
	}

	/**
	 * Wrapper for type hinting
	 *
	 * @return \OCA\Workflow_Notification\SendEmailPlugin
	 */
	protected function getPlugin() {
		return $this->getContainer()->query('OCA\Workflow_Notification\SendEmailPlugin');
	}

	/**
	 * Listen to file action events
	 *
	 * @param FileActionInterface $event
	 * @param string $eventTrigger
	 */
	public function fileActionListener(FileActionInterface $event, $eventTrigger) {
		$plugin = $this->getPlugin();
		$plugin->fileAction($event, $eventTrigger);
	}

	/**
	 * Validate the flow
	 *
	 * @param ValidateFlowInterface $event
	 */
	public function validateFlowListener(ValidateFlowInterface $event) {
		$plugin = $this->getPlugin();
		$plugin->listenValidateFlow($event);
	}

	/**
	 * Register the type to the workflow engine
	 *
	 * @param CollectTypesInterface $event
	 */
	public function collectTypesListener(CollectTypesInterface $event) {
		$plugin = $this->getPlugin();
		$plugin->collectTypes($event);
	}

	/**
	 * Load the plugins JS code
	 */
	public function loadAdminScripts() {
		\OCP\Util::addScript('workflow_notification', 'sendemailplugin');
	}
}
