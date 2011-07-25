<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows information about available update package servers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UpdateServerListPage extends SortablePage {
	// system
	public $templateName = 'updateServerList';
	public $neededPermissions = array('admin.system.package.canEditServer');
	public $defaultSortField = 'serverURL';
	public $deletedPackageUpdateServerID = 0;
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\package\update\server\PackageUpdateServerList';
	
	/**
	 * @see wcf\page\IPage::readParameters()
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
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'updateServers' => $this->objectList->getObjects(),
			'deletedPackageUpdateServerID' => $this->deletedPackageUpdateServerID
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.package.server.view');
		
		parent::show();
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'packageUpdateServerID':
			case 'serverURL':
			case 'status':
			case 'errorMessage':
			case 'lastUpdateTime':
			case 'packages': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
}
