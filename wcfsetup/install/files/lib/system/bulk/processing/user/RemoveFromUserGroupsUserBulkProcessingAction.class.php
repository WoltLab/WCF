<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserEditor;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for removing users from user groups.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
class RemoveFromUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public $inputName = 'removeFromUserGroupIDs';
	
	/**
	 * @inheritDoc
	 */
	protected function executeUserAction(UserEditor $user) {
		$user->removeFromGroups($this->userGroupIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		// the active user may not remove themselves from any user group
		// to avoid potential permission issues
		$userList->getConditionBuilder()->add('user_table.userID <> ?', [WCF::getUser()->userID]);
		
		return $userList;
	}
}
