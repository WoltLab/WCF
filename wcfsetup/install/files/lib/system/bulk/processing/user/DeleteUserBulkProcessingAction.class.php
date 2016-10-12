<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for deleting users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
class DeleteUserBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$users = $this->getAccessibleUsers($objectList);
		
		if (!empty($users)) {
			$userAction = new UserAction($users, 'delete');
			$userAction->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		// deny self deletion
		$userList->getConditionBuilder()->add('user_table.userID <> ?', [WCF::getUser()->userID]);
		
		return $userList;
	}
}
