<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows information about available update package servers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageUpdateServerListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canEditServer');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'serverURL';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('packageUpdateServerID', 'serverURL', 'status', 'errorMessage', 'lastUpdateTime', 'packages');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\package\update\server\PackageUpdateServerList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readObjects()
	 */
	public function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'packages' ? 'package_update_server.' : '') . $this->sortField.' '.$this->sortOrder;
		
		parent::readObjects();
	}
}
