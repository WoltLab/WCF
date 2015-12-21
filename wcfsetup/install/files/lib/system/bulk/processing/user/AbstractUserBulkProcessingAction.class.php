<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\system\bulk\processing\AbstractBulkProcessingAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract implementation of a user bulk processing action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractUserBulkProcessingAction extends AbstractBulkProcessingAction {
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getObjectList()
	 */
	public function getObjectList() {
		return new UserList();
	}
	
	/**
	 * Returns all users who the active user can access due to their user group
	 * assocition.
	 * 
	 * @param	\wcf\data\user\UserList		$userList
	 */
	protected function getAccessibleUsers(UserList $userList) {
		// fetch user group ids of all users
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID IN (?)', [ $userList->getObjectIDs() ]);
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		$groupIDs = [ ];
		while ($row = $statement->fetchArray()) {
			if (!isset($groupIDs[$row['userID']])) {
				$groupIDs[$row['userID']] = [ ];
			}
			
			$groupIDs[$row['userID']][] = $row['groupID'];
		}
		
		$users = [ ];
		foreach ($userList as $user) {
			if (empty($groupIDs[$user->userID]) || UserGroup::isAccessibleGroup($groupIDs[$user->userID])) {
				$users[$user->userID] = $user;
			}
		}
		
		return $users;
	}
}
