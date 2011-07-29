<?php
namespace wcf\acp\page;
use wcf\system\menu\acp\ACPMenu;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class UserGroupListPage extends SortablePage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'userGroupList';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditGroup', 'admin.user.canDeleteGroup');
	
	/**
	 * @see wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'groupName';
	
	/**
	 * @see wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('groupID', 'groupName', 'groupType', 'members');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\user\group\UserGroupList';
	
	/**
	 * indicates if a group has just been deleted
	 * @var	integer
	 */
	public $deletedGroups = 0;
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// detect group deletion
		if (isset($_REQUEST['deletedGroups'])) {
			$this->deletedGroups = intval($_REQUEST['deletedGroups']);
		}
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */	
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_user_to_group WHERE groupID = user_group.groupID) AS members";
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'members' ? 'user_group.' : '').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'deletedGroups' => $this->deletedGroups
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.group.view');
		
		parent::show();
	}
}
