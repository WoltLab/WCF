<?php
namespace wcf\acp\page;
use wcf\data\user\group\UserGroupList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	UserGroupList		$objectList
 */
class UserGroupListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditGroup', 'admin.user.canDeleteGroup'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'groupName';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['groupID', 'groupName', 'groupType', 'members', 'priority'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UserGroupList::class;
	
	/**
	 * indicates if a group has just been deleted
	 * @var	integer
	 */
	public $deletedGroups = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// detect group deletion
		if (isset($_REQUEST['deletedGroups'])) {
			$this->deletedGroups = intval($_REQUEST['deletedGroups']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_user_to_group WHERE groupID = user_group.groupID) AS members";
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'members' ? 'user_group.' : '').$this->sortField." ".$this->sortOrder;
		
		parent::readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'deletedGroups' => $this->deletedGroups
		]);
	}
}
