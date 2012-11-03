<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\package\PackageDependencyHandler;

/**
 * Shows cronjob log information.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class CronjobLogListPage extends SortablePage {
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.cronjob.canEditCronjob');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @see	wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'execTime';
	
	/**
	 * @see	wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see	wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('cronjobID', 'className', 'description', 'execTime', 'success');
	
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
		$this->objectList->getConditionBuilder()->add("cronjob_log.cronjobID IN (SELECT cronjobID FROM wcf".WCF_N."_cronjob WHERE packageID IN (?))", array(PackageDependencyHandler::getInstance()->getDependencies()));
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = (($this->sortField == 'className' || $this->sortField == 'description') ? 'cronjob.' : 'cronjob_log.').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item.
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.log.cronjob');
		
		parent::show();
	}
}
