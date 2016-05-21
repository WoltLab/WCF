<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserEditor;

/**
 * Bulk processing action implementation for assigning users to user groups.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
class AssignToUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction {
	/**
	 * @see	\wcf\system\bulk\processing\user\AbstractUserGroupsUserBulkProcessingAction::$inputName
	 */
	public $inputName = 'assignToUserGroupIDs';
	
	/**
	 * @see	\wcf\system\bulk\processing\user\AbstractUserGroupsUserBulkProcessingAction::executeUserAction()
	 */
	protected function executeUserAction(UserEditor $user) {
		$user->addToGroups($this->userGroupIDs, false, false);
	}
}
