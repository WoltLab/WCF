<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\package\PackageList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageListPage extends SortablePage {
	// system
	public $templateName = 'packageList';
	public $neededPermissions = array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage');
	public $itemsPerPage = 50;
	public $defaultSortField = 'packageType';
	public $defaultSortOrder = 'DESC';
	
	/**
	 * list of packages
	 * 
	 * @var	PackageList
	 */
	public $packageList = null;
	
	/**
	 * class name for DatabaseObjectList
	 * 
	 * @var	string
	 */	
	public $objectListClassName = 'wcf\data\package\PackageList';
	
	/**
	 * @see	wcf\page\MuletipleLinkPage::readObjects()
	 */	
	protected function readObjects() {
		$this->sqlOrderBy = 'package.'.($this->sortField == 'packageType' ? 'standalone '.$this->sortOrder.', package.parentPackageID '.$this->sortOrder : $this->sortField.' '.$this->sortOrder).($this->sortField != 'packageName' ? ', package.packageName ASC' : '');
		
		parent::readObjects();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign('packages', $this->objectList->getObjects());
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.view');
		
		parent::show();
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'packageID':
			case 'package':
			case 'packageDir':
			case 'packageName':
			case 'instanceNo':
			case 'packageDescription':
			case 'packageVersion':
			case 'packageDate':
			case 'packageURL':
			case 'parentPackageID':
			case 'isUnique':
			case 'standalone':
			case 'author':
			case 'authorURL':
			case 'installDate':
			case 'updateDate': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
}
