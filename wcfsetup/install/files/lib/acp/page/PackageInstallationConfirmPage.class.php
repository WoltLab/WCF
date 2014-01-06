<?php
namespace wcf\acp\page;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows a confirmation page prior to start installing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageInstallationConfirmPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.install';
	
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
	 * @var	\wcf\system\package\PackageInstallationDispatcher
	 */
	public $packageInstallationDispatcher = null;
	
	/**
	 * package installation queue object
	 * @var	\wcf\data\package\installation\queue\PackageInstallationQueue
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
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['queueID'])) $this->queueID = intval($_REQUEST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		if (!$this->queue->queueID || $this->queue->done) {
			throw new IllegalLinkException();
		}
		
		if ($this->queue->action == 'install') {
			WCF::getSession()->checkPermissions(array('admin.system.package.canInstallPackage'));
		}
		else {
			WCF::getSession()->checkPermissions(array('admin.system.package.canUpdatePackage'));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
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
					if ($requirement['action'] === 'update') {
						$requirement['status'] = 'missingVersion';
						$requirement['existingVersion'] = $this->openRequirements[$requirement['name']]['existingVersion'];
					}
					$this->missingPackages++;
				}
				else {
					$requirement['status'] = 'delivered';
					$packageArchive = new PackageArchive($this->packageInstallationDispatcher->getArchive()->extractTar($requirement['file']));
					$packageArchive->openArchive();
					
					// make sure that the delivered package is correct
					if ($requirement['name'] != $packageArchive->getPackageInfo('name')) {
						$requirement['status'] = 'invalidDeliveredPackage';
						$requirement['deliveredPackage'] = $packageArchive->getPackageInfo('name');
						$this->missingPackages++;
					}
					else if (isset($requirement['minversion'])) {
						// make sure that the delivered version is sufficient
						if (Package::compareVersion($requirement['minversion'], $packageArchive->getPackageInfo('version')) > 0) {
							$requirement['deliveredVersion'] = $packageArchive->getPackageInfo('version');
							$requirement['status'] = 'missingVersion';
							$this->missingPackages++;
						}
					}
				}
			}
			else {
				$requirement['status'] = 'installed';
			}
			
			$requirement['package'] = PackageCache::getInstance()->getPackageByIdentifier($requirement['name']);
		}
		
		unset($requirement);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'archive' => $this->packageInstallationDispatcher->getArchive(),
			'requiredPackages' => $this->requirements,
			'missingPackages' => $this->missingPackages,
			'excludingPackages' => $this->packageInstallationDispatcher->getArchive()->getConflictedExcludingPackages(),
			'excludedPackages' => $this->packageInstallationDispatcher->getArchive()->getConflictedExcludedPackages(),
			'queue' => $this->queue
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
