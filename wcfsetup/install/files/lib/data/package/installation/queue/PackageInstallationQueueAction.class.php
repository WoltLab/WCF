<?php
namespace wcf\data\package\installation\queue;
use wcf\data\package\Package;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes package installation queue-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.queue
 * @category	Community Framework
 */
class PackageInstallationQueueAction extends AbstractDatabaseObjectAction {
	/**
	 * package the prepared queue belongs to
	 * @var	wcf\data\package\Package
	 */
	protected $package = null;
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\installation\queue\PackageInstallationQueueEditor';
	
	/**
	 * Validates the 'prepareQueue' action:
	 */
	public function validatePrepareQueue() {
		$this->readInteger('packageID');
		
		$this->package = new Package($this->parameters['packageID']);
		if (!$this->package->packageID) {
			throw new UserInputException('packageID');
		}
		
		if (!isset($this->parameters['action']) || !in_array($this->parameters['action'], array('install', 'update', 'uninstall', 'rollback'))) {
			throw new UserInputException('action');
		}
	}
	
	/**
	 * Prepares a new package installation queue.
	 * 
	 * @return	array<integer>
	 */
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
