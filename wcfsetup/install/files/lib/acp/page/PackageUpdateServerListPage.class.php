<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows information about available update package servers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageUpdateServerListPage extends SortablePage {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.list';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canEditServer');
	
	/**
	 * @see	wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'serverURL';
	
	/**
	 * @see	wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('packageUpdateServerID', 'serverURL', 'status', 'errorMessage', 'lastUpdateTime', 'packages');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\package\update\server\PackageUpdateServerList';
	
	/**
	 * id of a package update server that has just been deleted
	 * @var	integer
	 */
	public $deletedPackageUpdateServerID = 0;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['deletedPackageUpdateServerID'])) $this->deletedPackageUpdateServerID = intval($_REQUEST['deletedPackageUpdateServerID']);
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	public function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'packages' ? 'package_update_server.' : '') . $this->sortField.' '.$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'deletedPackageUpdateServerID' => $this->deletedPackageUpdateServerID
		));
	}
}
