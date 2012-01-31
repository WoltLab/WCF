<?php
namespace wcf\data\package\installation\queue;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\package\Package;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes package installation queue-related actions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.queue
 * @category 	Community Framework
 */
class PackageInstallationQueueAction extends AbstractDatabaseObjectAction {
	protected $package = null;
	protected $packageID = 0;
	
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\installation\queue\PackageInstallationQueueEditor';
	
	public function validatePrepareQueue() {
		if (isset($this->parameters['packageID'])) $this->packageID = intval($this->parameters['packageID']);
		
		$this->package = new Package($this->packageID);
		if (!$this->package->packageID) {
			throw new ValidateActionException('Invalid package id');
		}
		
		if (!isset($this->parameters['action']) || !in_array($this->parameters['action'], array('install', 'update', 'uninstall', 'rollback'))) {
			throw new ValidateActionException('Invalid or missing action');
		}
	}
	
	public function prepareQueue() {
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		$queue = PackageInstallationQueueEditor::create(array(
			'processNo' => $processNo,
			'userID' => WCF::getUser()->userID,
			'package' => $this->package->package,
			'packageName' => $this->package->packageName,
			'packageID' => $this->package->packageID,
			'action' => $this->parameters['action'],
			'installationType' => 'other'
		));
		
		return array(
			'queueID' => $queue->queueID
		);
	}
}
