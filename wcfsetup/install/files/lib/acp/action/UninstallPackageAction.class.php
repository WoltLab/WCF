<?php
namespace wcf\acp\action;
use wcf\action\AbstractDialogAction;
use wcf\data\package\Package;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package uninstallation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UninstallPackageAction extends InstallPackageAction {
	/**
	 * active package id
	 *
	 * @var	integer
	 */
	protected $packageID = 0;
	
	// system
	public $templateName = 'packageUninstallationStep';
	
	/**
	 * @see wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		AbstractDialogAction::readParameters();
		
		if (isset($_POST['node'])) $this->node = StringUtil::trim($_POST['node']);
		
		if (isset($_POST['packageID'])) {
			$this->packageID = intval($_POST['packageID']);
		}
		else {
			if (isset($_POST['queueID'])) $this->queueID = intval($_POST['queueID']);
			$this->queue = new PackageInstallationQueue($this->queueID);
			
			if (!$this->queue->queueID) {
				throw new IllegalLinkException();
			}
			
			$this->installation = new PackageUninstallationDispatcher($this->queue);
		}
	}
	
	/**
	 * Prepares the uninstallation process.
	 */
	protected function stepPrepare() {
		$package = new Package($this->packageID);
		if (!$package->packageID) {
			throw new IllegalLinkException();
		}
		
		if (PackageUninstallationDispatcher::hasDependencies($package->packageID)) {
			throw new SystemException('hasDependencies');
		}
		else {
			// get new process no
			$processNo = PackageInstallationQueue::getNewProcessNo();
			
			// create queue
			$queue = PackageInstallationQueueEditor::create(array(
				'processNo' => $processNo,
				'userID' => WCF::getUser()->userID,
				'packageName' => $package->getName(),
				'packageID' => $package->packageID,
				'action' => 'uninstall',
				'cancelable' => 0
			));
			
			// initialize uninstallation
			$this->installation = new PackageUninstallationDispatcher($queue);
			
			$this->installation->nodeBuilder->purgeNodes();
			$this->installation->nodeBuilder->buildNodes();
			
			WCF::getTPL()->assign(array(
				'queue' => $queue
			));
			
			$this->data = array(
				'template' => WCF::getTPL()->fetch($this->templateName),
				'step' => 'uninstall',
				'node' => $this->installation->nodeBuilder->getNextNode(),
				'currentAction' => WCF::getLanguage()->get('wcf.package.installation.step.uninstalling'),
				'progress' => 0,
				'queueID' => $queue->queueID
			);
		}
	}
	
	/**
	 * Uninstalls node components and returns next node.
	 *
	 * @param	string		$node
	 * @return	string
	 */
	public function stepUninstall() {
		$node = $this->installation->uninstall($this->node);
		
		if ($node == '') {
			// remove node data
			$this->installation->nodeBuilder->purgeNodes();
			
			// TODO: Show 'success' template at this point
			$this->data = array(
				'progress' => 100,
				'step' => 'success'
			);
			return;
		}
		
		// continue with next node
		$this->data = array(
			'step' => 'uninstall',
			'node' => $node,
			'progress' => $this->installation->nodeBuilder->calculateProgress($this->node)
		);
	}
	
	/**
	 * @see	AbstractDialogAction::validateStep()
	 */
	protected function validateStep() {
		switch ($this->step) {
			case 'prepare':
			case 'uninstall':
				continue;
			break;
			
			default:
				die(print_r($_POST, true));
				throw new IllegalLinkException();
			break;
		}
	}
}
