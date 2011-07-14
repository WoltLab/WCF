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
	public $neededPermissions = array('admin.system.cronjobs.canEditCronjob', 'admin.system.cronjobs.canDeleteCronjob', 'admin.system.cronjobs.canEnableDisableCronjob');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'description';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\cronjob\CronjobList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */	
	public function readObjects() {
		$this->objectList->getConditionBuilder()->add("cronjob.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$this->sqlOrderBy = "cronjob.".$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'cronjobs' => $this->objectList->getObjects()
		));
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'description':
			case 'cronjobID':
			case 'nextExec':
			case 'startMinute':
			case 'startHour':
			case 'startDom':
			case 'startMonth':
			case 'startDow': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see wcf\page\Page::show()
	 */
	public function show() {
		// set active menu item.
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.cronjobs.view');
		
		parent::show();
	}
}
?>
