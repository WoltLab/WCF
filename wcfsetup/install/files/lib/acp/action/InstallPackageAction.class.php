<?php
namespace wcf\acp\action;
use wcf\action\AbstractDialogAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package installation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class InstallPackageAction extends AbstractDialogAction {
	/**
	 * current node
	 * @var	string
	 */
	public $node = '';
	
	/**
	 * PackageInstallationDispatcher object
	 * @var	wcf\system\package\PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * PackageInstallationQueue object
	 * @var	wcf\data\package\installation\queue\PackageInstallationQueue
	 */
	public $queue = null;
	
	/**
	 * current queue id
	 * @var	integer
	 */
	public $queueID = 0;
	
	/**
	 * @see	wcf\action\AbstractDialogAction::$templateName
	 */
	public $templateName = 'packageInstallationStep';
	
	/**
	 * @see wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['node'])) $this->node = StringUtil::trim($_POST['node']);
		if (isset($_POST['queueID'])) $this->queueID = intval($_POST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		
		if (!$this->queue->queueID) {
			throw new IllegalLinkException();
		}
		
		$this->installation = new PackageInstallationDispatcher($this->queue);
	}
	
	/**
	 * Executes installation based upon nodes.
	 */
	protected function stepInstall() {
		$step = $this->installation->install($this->node);
		$queueID = $this->installation->nodeBuilder->getQueueByNode($this->installation->queue->processNo, $step->getNode());
		
		if ($step->hasDocument()) {
			$this->data = array(
				'currentAction' => $this->getCurrentAction($queueID),
				'innerTemplate' => $step->getTemplate(),
				'node' => $step->getNode(),
				'progress' => $this->installation->nodeBuilder->calculateProgress($this->node),
				'step' => 'install',
				'queueID' => $queueID
			);
		}
		else {
			if ($step->getNode() == '') {
				// perform final actions
				$this->installation->completeSetup();
				$this->finalize();
				
				switch (PACKAGE_ID) {
					// redirect to application if not already within one
					case 0: // during WCFSetup
					case 1:
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
					break;
					
					default:
						$packageID = PACKAGE_ID;
					break;
				}
					
				// get domain path
				$sql = "SELECT	domainName, domainPath
					FROM	wcf".WCF_N."_application
					WHERE	packageID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array($packageID));
				$row = $statement->fetchArray();
				
				// build redirect location
				$location = $row['domainName'] . $row['domainPath'] . 'acp/index.php/PackageList/' . SID_ARG_1ST;
				@file_put_contents(WCF_DIR . '__installPackage.txt', "packageID = ".PACKAGE_ID ."\n" . $row['domainName'] . "\n" . $row['domainPath'] . "\n" . $location);
				
				// show success
				$this->data = array(
					'currentAction' => $this->getCurrentAction(null),
					'progress' => 100,
					'redirectLocation' => $location,
					'step' => 'success'
				);
				return;
			}
			
			// continue with next node
			$this->data = array(
				'currentAction' => $this->getCurrentAction($queueID),
				'step' => 'install',
				'node' => $step->getNode(),
				'progress' => $this->installation->nodeBuilder->calculateProgress($this->node),
				'queueID' => $queueID
			);
		}
	}
	
	/**
	 * Prepares the installation process.
	 */
	protected function stepPrepare() {
		// update package information
		$this->installation->updatePackage();
		
		// clean-up previously created nodes
		$this->installation->nodeBuilder->purgeNodes();
		
		// create node tree
		$this->installation->nodeBuilder->buildNodes();
		$nextNode = $this->installation->nodeBuilder->getNextNode();
		$queueID = $this->installation->nodeBuilder->getQueueByNode($this->installation->queue->processNo, $nextNode);
		
		WCF::getTPL()->assign(array(
			'packageName' => $this->installation->queue->packageName
		));
		
		$this->data = array(
			'template' => WCF::getTPL()->fetch($this->templateName),
			'step' => 'install',
			'node' => $nextNode,
			'currentAction' => $this->getCurrentAction($queueID),
			'progress' => 0,
			'queueID' => $queueID
		);
	}
	
	/**
	 * @see	wcf\action\AbstractDialogAction\AbstractDialogAction::validateStep()
	 */
	protected function validateStep() {
		switch ($this->step) {
			case 'install':
			case 'prepare':
				continue;
			break;
			
			default:
				throw new IllegalLinkException();
			break;
		}
	}
	
	/**
	 * Returns current action by queue id.
	 * 
	 * @param	integer		$queueID
	 * @return	string
	 */
	protected function getCurrentAction($queueID) {
		if ($queueID === null) {
			// success message
			$currentAction = WCF::getLanguage()->get('wcf.acp.package.installation.step.install.success');
		}
		else {
			// build package name
			$packageName = $this->installation->nodeBuilder->getPackageNameByQueue($queueID);
			$currentAction = WCF::getLanguage()->getDynamicVariable('wcf.acp.package.installation.step.install', array('packageName' => $packageName));
		}
		
		return $currentAction;
	}
	
	/**
	 * Clears resources after successful installation.
	 */
	protected function finalize() {
		// clear cache
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	isApplication = 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		while ($row = $statement->fetchArray()) {
			$cacheDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir'] . 'cache/');
			
			CacheHandler::getInstance()->clear($cacheDir, '*.php');
		}
	}
}
