<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\UserAction;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;

/**
 * Executes automatic user group assignments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class UserGroupAssignmentCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
		$usersToGroup = array();
		foreach ($assignments as $assignment) {
			if (!isset($usersToGroup[$assignment->groupID])) {
				$usersToGroup[$assignment->groupID] = array();
			}
			
			$usersToGroup[$assignment->groupID] = array_merge($usersToGroup[$assignment->groupID], UserGroupAssignmentHandler::getInstance()->getUsers($assignment));
		}
		
		foreach ($usersToGroup as $groupID => $users) {
			$userAction = new UserAction(array_unique($users), 'addToGroups', array(
				'addDefaultGroups' => false,
				'deleteOldGroups' => false,
				'groups' => array($groupID)
			));
			$userAction->executeAction();
		}
	}
}
