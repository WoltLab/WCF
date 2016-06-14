<?php
namespace wcf\data\package\installation\queue;
use wcf\data\package\Package;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes package installation queue-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Queue
 * 
 * @method	PackageInstallationQueue		create()
 * @method	PackageInstallationQueueEditor[]	getObjects()
 * @method	PackageInstallationQueueEditor		getSingleObject()
 */
class PackageInstallationQueueAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PackageInstallationQueueEditor::class;
	
	/**
	 * queue of the canceled installation
	 * @var	\wcf\data\package\installation\queue\PackageInstallationQueueEditor
	 */
	protected $queue = null;
	
	/**
	 * package the prepared queue belongs to
	 * @var	\wcf\data\package\Package
	 */
	protected $package = null;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['cancelInstallation', 'prepareQueue'];
	
	/**
	 * Validates the 'prepareQueue' action:
	 */
	public function validatePrepareQueue() {
		$this->readInteger('packageID');
		
		$this->package = new Package($this->parameters['packageID']);
		if (!$this->package->packageID) {
			throw new UserInputException('packageID');
		}
		
		if (!isset($this->parameters['action']) || !in_array($this->parameters['action'], ['install', 'update', 'uninstall', 'rollback'])) {
			throw new UserInputException('action');
		}
	}
	
	/**
	 * Prepares a new package installation queue.
	 * 
	 * @return	integer[]
	 */
	public function prepareQueue() {
		$processNo = PackageInstallationQueue::getNewProcessNo();
		
		$queue = PackageInstallationQueueEditor::create([
			'processNo' => $processNo,
			'userID' => WCF::getUser()->userID,
			'package' => $this->package->package,
			'packageName' => $this->package->packageName,
			'packageID' => $this->package->packageID,
			'action' => $this->parameters['action'],
			'installationType' => 'other'
		]);
		
		return [
			'queueID' => $queue->queueID
		];
	}
	
	/**
	 * Validates the 'cancelInstallation' action.
	 */
	public function validateCancelInstallation() {
		// check permissions
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		
		// validate queue
		$this->queue = $this->getSingleObject();
		if ($this->queue->parentQueueID || $this->queue->done) {
			throw new UserInputException('objectIDs');
		}
		
		if ($this->queue->userID != WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Cancels a certain installation.
	 */
	public function cancelInstallation() {
		@unlink($this->queue->archive);
		
		$this->queue->delete();
		
		return [
			'url' => LinkHandler::getInstance()->getLink('PackageList')
		];
	}
}
