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

namespace OCA\Workflow_DocToPdf;

use OCA\Workflow\PublicAPI\Event\CollectTypesInterface;
use OCA\Workflow\PublicAPI\Event\FileActionInterface;
use OCP\BackgroundJob\IJobList;

class ConverterPlugin {

	/** @var IJobList */
	protected $jobList;

	/**
	 * ConverterPlugin constructor.
	 *
	 * @param IJobList $jobList
	 */
	public function __construct(IJobList $jobList) {
		$this->jobList = $jobList;
	}

	/**
	 * @param FileActionInterface $event
	 */
	public function listen(FileActionInterface $event) {
		$flow = $event->getFlow();
		if ($flow->getType() !== 'workflow_doctopdf') {
			return;
		}

		$this->jobList->add('OCA\Workflow_DocToPdf\BackgroundJob', $event->getPath());
	}

	/**
	 * @param CollectTypesInterface $event
	 */
	public function collectTypes(CollectTypesInterface $event) {
		$event->addType('workflow_doctopdf', 'Doc to Pdf Convertor');
	}
}
