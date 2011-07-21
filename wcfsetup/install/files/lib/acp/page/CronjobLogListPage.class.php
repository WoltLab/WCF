<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\SortablePage;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\WCF;

/**
 * Shows cron jobs log information.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CronjobLogListPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'cronjobLogList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.cronjobs.canEditCronjob');
	
	/**
	 * @see wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'execTime';
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\cronjob\log\CronjobLogList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "cronjob.*";
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_cronjob cronjob ON (cronjob.cronjobID = cronjob_log.cronjobID)";
		$this->objectList->getConditionBuilder()->add("cronjob_log.cronjobID IN (SELECT cronjobID FROM wcf".WCF_N."_cronjob WHERE packageID IN (?))", array(PackageDependencyHandler::getDependencies()));
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = (($this->sortField == 'classPath' || $this->sortField == 'description') ? 'cronjob.' : 'cronjob_log.').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'cronjobLogs' => $this->objectList->getObjects()
		));
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'cronjobID':
			case 'classPath':
			case 'description':
			case 'execTime':
			case 'success': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item.
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.cronjobs.showLog');
		
		parent::show();
	}
}
