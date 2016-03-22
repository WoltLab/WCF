<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserEditor;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for removing users from user groups.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
class RemoveFromUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction {
	/**
	 * @see	\wcf\system\bulk\processing\user\AbstractUserGroupsUserBulkProcessingAction::$inputName
	 */
	public $inputName = 'removeFromUserGroupIDs';
	
	/**
	 * @see	\wcf\system\bulk\processing\user\AbstractUserGroupsUserBulkProcessingAction::executeUserAction()
	 */
	protected function executeUserAction(UserEditor $user) {
		$user->removeFromGroups($this->userGroupIDs);
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getObjectList()
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		// the active user may not remove themselves from any user group
		// to avoid potential permission issues
		$userList->getConditionBuilder()->add('user_table.userID <> ?', array(WCF::getUser()->userID));
		
		return $userList;
	}
}
