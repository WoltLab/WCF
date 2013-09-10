<?php
namespace wcf\acp\action;
use wcf\action\AbstractDialogAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\Package;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package uninstallation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class UninstallPackageAction extends InstallPackageAction {
	/**
	 * active package id
	 * @var	integer
	 */
	protected $packageID = 0;
	
	/**
	 * @see	wcf\action\AbstractDialogAction::$templateName
	 */
	public $templateName = 'packageUninstallationStep';
	
	/**
	 * @see	wcf\action\IAction::readParameters()
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
		if (!$package->packageID || !$package->canUninstall()) {
			throw new IllegalLinkException();
		}
		
		// get new process no
		$processNo = PackageInstallationQueue::getNewProcessNo();
			
		// create queue
		$queue = PackageInstallationQueueEditor::create(array(
			'processNo' => $processNo,
			'userID' => WCF::getUser()->userID,
			'packageName' => $package->getName(),
			'packageID' => $package->packageID,
			'action' => 'uninstall'
		));
		
		// initialize uninstallation
		$this->installation = new PackageUninstallationDispatcher($queue);
		
		$this->installation->nodeBuilder->purgeNodes();
		$this->installation->nodeBuilder->buildNodes();
		
		WCF::getTPL()->assign(array(
			'queue' => $queue
		));
		
		$queueID = $this->installation->nodeBuilder->getQueueByNode($queue->processNo, $this->installation->nodeBuilder->getNextNode());
		$this->data = array(
			'template' => WCF::getTPL()->fetch($this->templateName),
			'step' => 'uninstall',
			'node' => $this->installation->nodeBuilder->getNextNode(),
			'currentAction' => $this->getCurrentAction($queueID),
			'progress' => 0,
			'queueID' => $queueID
		);
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
			$this->finalize();
			
			// redirect to application if not already within one
			if (PACKAGE_ID == 1) {
				// select first installed application
				$sql = "SELECT		packageID
					FROM		wcf".WCF_N."_package
					WHERE		packageID <> 1
							AND isApplication = 1
					ORDER BY	installDate ASC";
				$statement = WCF::getDB()->prepareStatement($sql, 1);
				$statement->execute();
				$row = $statement->fetchArray();
				$packageID = ($row === false) ? 1 : $row['packageID'];
			}
			else {
				$packageID = PACKAGE_ID;
			}
			
			// get domain path
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_application
				WHERE	packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageID));
			$application = $statement->fetchObject('wcf\data\application\Application');
			
			// build redirect location
			$location = $application->getPageURL() . 'acp/index.php/PackageList/' . SID_ARG_1ST;
			
			// show success
			$this->data = array(
				'currentAction' => WCF::getLanguage()->get('wcf.acp.package.uninstallation.step.success'),
				'progress' => 100,
				'redirectLocation' => $location,
				'step' => 'success'
			);
			return;
		}
		
		// continue with next node
		$queueID = $this->installation->nodeBuilder->getQueueByNode($this->installation->queue->processNo, $this->installation->nodeBuilder->getNextNode($this->node));
		$this->data = array(
			'step' => 'uninstall',
			'node' => $node,
			'progress' => $this->installation->nodeBuilder->calculateProgress($this->node),
			'queueID' => $queueID
		);
	}
	
	/**
	 * @see	wcf\action\AbstractDialogAction::validateStep()
	 */
	protected function validateStep() {
		switch ($this->step) {
			case 'prepare':
			case 'uninstall':
				continue;
			break;
			
			default:
				throw new IllegalLinkException();
			break;
		}
	}
	
	/**
	 * @see	wcf\acp\action\InstallPackageAction::getCurrentAction()
	 */
	protected function getCurrentAction($queueID) {
		if ($queueID === null) {
			// success message
			$currentAction = WCF::getLanguage()->get('wcf.acp.package.uninstallation.step.' . $this->queue->action . '.success');
		}
		else {
			// build package name
			$packageName = $this->installation->nodeBuilder->getPackageNameByQueue($queueID);
			$installationType = $this->installation->nodeBuilder->getInstallationTypeByQueue($queueID);
			$currentAction = WCF::getLanguage()->getDynamicVariable('wcf.acp.package.uninstallation.step.'.$installationType, array('packageName' => $packageName));
		}
	
		return $currentAction;
	}
}
