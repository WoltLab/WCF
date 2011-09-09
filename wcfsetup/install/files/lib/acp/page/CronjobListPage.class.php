<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\SortablePage;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Shows information about configured cron jobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CronjobListPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'cronjobList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.cronjob.canEditCronjob', 'admin.system.cronjob.canDeleteCronjob', 'admin.system.cronjob.canEnableDisableCronjob');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'description';
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('description', 'cronjobID', 'nextExec', 'startMinute', 'startHour', 'startDom', 'startMonth', 'startDow');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\cronjob\CronjobList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */	
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("cronjob.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$this->sqlOrderBy = "cronjob.".$this->sortField." ".$this->sortOrder;
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'cronjob' => $this->objectList->getObjects()
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item.
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.cronjob.list');
		
		parent::show();
	}
}
