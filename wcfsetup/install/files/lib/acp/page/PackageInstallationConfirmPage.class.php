<?php
namespace wcf\acp\page;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows a confirmation page prior to start installing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageInstallationConfirmPage extends AbstractPage {
	/**
	 * number of missing packages
	 * @var	integer
	 */
	public $missingPackages = 0;
	
	/**
	 * list of unsatisfied requirements
	 * @var	array<array>
	 */
	public $openRequirements = array();
	
	/**
	 * package installation dispatcher object
	 * @var	wcf\system\package\PackageInstallationDispatcher
	 */
	public $packageInstallationDispatcher = null;
	
	/**
	 * package installation queue object
	 * @var	wcf\data\package\installation\queue\PackageInstallationQueue
	 */
	public $queue = null;
	
	/**
	 * queue id
	 * @var	integer
	 */
	public $queueID = 0;
	
	/**
	 * list of requirements
	 * @var	array<array>
	 */
	public $requirements = array();
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['queueID'])) $this->queueID = intval($_REQUEST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		if (!$this->queue->queueID || $this->queue->done) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->packageInstallationDispatcher = new PackageInstallationDispatcher($this->queue);
		
		// get requirements
		$this->requirements = $this->packageInstallationDispatcher->getArchive()->getRequirements();
		$this->openRequirements = $this->packageInstallationDispatcher->getArchive()->getOpenRequirements();
		
		foreach ($this->requirements as &$requirement) {
			if (isset($this->openRequirements[$requirement['name']])) {
				$requirement['status'] = 'missing';
				$requirement['action'] = $this->openRequirements[$requirement['name']]['action'];
				
				if (!isset($requirement['file'])) {
					if ($this->openRequirements[$requirement['name']]['action'] === 'update') {
						$requirement['status'] = 'missingVersion';
						$requirement['existingVersion'] = $this->openRequirements[$requirement['name']]['existingVersion'];
					}
					$this->missingPackages++;
				}
				else {
					$requirement['status'] = 'delivered';
				}
			}
			else {
				$requirement['status'] = 'installed';
			}
		}
		unset($requirement);
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'archive' => $this->packageInstallationDispatcher->getArchive(),
			'requiredPackages' => $this->requirements,
			'missingPackages' => $this->missingPackages,
			'excludingPackages' => $this->packageInstallationDispatcher->getArchive()->getConflictedExcludingPackages(),
			'excludedPackages' => $this->packageInstallationDispatcher->getArchive()->getConflictedExcludedPackages(),
			'queueID' => $this->queue->queueID
		));
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.install');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		if ($this->action == 'install') {
			WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
		}
		else {
			WCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
		}
		
		parent::show();
	}
}
