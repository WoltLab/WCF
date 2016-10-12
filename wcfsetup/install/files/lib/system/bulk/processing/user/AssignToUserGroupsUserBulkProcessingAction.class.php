<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserEditor;

/**
 * Bulk processing action implementation for assigning users to user groups.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
class AssignToUserGroupsUserBulkProcessingAction extends AbstractUserGroupsUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public $inputName = 'assignToUserGroupIDs';
	
	/**
	 * @inheritDoc
	 */
	protected function executeUserAction(UserEditor $user) {
		$user->addToGroups($this->userGroupIDs, false, false);
	}
}
