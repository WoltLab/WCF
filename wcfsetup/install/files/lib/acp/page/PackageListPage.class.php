<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\data\package\update\server\PackageUpdateServer;

/**
 * Shows a list of all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 50;
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'packageType';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * package id for uninstallation
	 * @var	integer
	 */
	public $packageID = 0;
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('packageID', 'package', 'packageDir', 'packageName', 'packageDescription', 'packageDate', 'packageURL', 'isApplication', 'author', 'authorURL', 'installDate', 'updateDate');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\package\PackageList';
	
	/**
	 * list of WCF major releases covered by existing store update servers
	 * @var	array<string>
	 */
	public $wcfMajorReleases = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['packageID'])) $this->packageID = intval($_GET['packageID']);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$updateServers = PackageUpdateServer::getActiveUpdateServers();
		foreach ($updateServers as $updateServer) {
			if (preg_match('~^https?://store.woltlab.com/(maelstrom|typhoon)/$~', $updateServer->serverURL, $matches)) {
				$this->wcfMajorReleases[] = $matches[1];
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'packageID' => $this->packageID,
			'wcfMajorReleases' => $this->wcfMajorReleases
		));
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'package.'.($this->sortField == 'packageType' ? 'isApplication '.$this->sortOrder : $this->sortField.' '.$this->sortOrder).($this->sortField != 'packageName' ? ', package.packageName ASC' : '');
		
		parent::readObjects();
	}
}
