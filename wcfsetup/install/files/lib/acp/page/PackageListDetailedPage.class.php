<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\menu\acp\ACPMenu;

/**
 * Shows a list of all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PackageListDetailedPage extends SortablePage {
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 50;
	
	/**
	 * @see	wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'packageType';
	
	/**
	 * @see	wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see	wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('packageID', 'package', 'packageDir', 'packageName', 'instanceNo', 'packageDescription', 'packageDate', 'packageURL', 'parentPackageID', 'isUnique', 'isApplication', 'author', 'authorURL', 'installDate', 'updateDate');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\package\PackageList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = 'package.'.($this->sortField == 'packageType' ? 'isApplication '.$this->sortOrder.', package.parentPackageID '.$this->sortOrder : $this->sortField.' '.$this->sortOrder).($this->sortField != 'packageName' ? ', package.packageName ASC' : '');
		
		parent::readObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.list');
		
		parent::show();
	}
}
