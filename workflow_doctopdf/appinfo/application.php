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

namespace OCA\Workflow_DocToPdf\AppInfo;

use OCA\Workflow\PublicAPI\Event\CollectTypesInterface;
use OCA\Workflow\PublicAPI\Event\FileActionInterface;

class Application extends \OCP\AppFramework\App {

	/**
	 * Application constructor.
	 */
	public function __construct() {
		parent::__construct('workflow_doctopdf');
	}

	/**
	 * Register the app to several events
	 */
	public function registerListeners() {
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();

		$dispatcher->addListener('OCA\Workflow\Engine::' . FileActionInterface::FILE_CREATE, [$this, 'fileActionListener']);
		$dispatcher->addListener('OCA\Workflow\Engine::' . FileActionInterface::FILE_UPDATE, [$this, 'fileActionListener']);
		$dispatcher->addListener('OCA\Workflow\Engine::' . CollectTypesInterface::TYPES_COLLECT, [$this, 'collectTypesListener']);
		$dispatcher->addListener('OC\Settings\Admin::loadAdditionalScripts', [$this, 'loadAdminScripts']);
	}

	/**
	 * Wrapper for type hinting
	 *
	 * @return \OCA\Workflow_DocToPdf\ConverterPlugin
	 */
	protected function getPlugin() {
		return $this->getContainer()->query('OCA\Workflow_DocToPdf\ConverterPlugin');
	}

	/**
	 * Listen to file action events
	 *
	 * @param FileActionInterface $event
	 */
	public function fileActionListener(FileActionInterface $event) {
		$plugin = $this->getPlugin();
		$plugin->listen($event);
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
		\OCP\Util::addScript('workflow_doctopdf', 'converterplugin');
	}
}
