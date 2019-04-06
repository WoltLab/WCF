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
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class UserGroupAssignmentCronjob extends AbstractCronjob {
	const MAXIMUM_ASSIGNMENTS = 1000;
	
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
		$usersToGroup = [];
		
		$assignmentCount = 0;
		foreach ($assignments as $assignment) {
			if (!isset($usersToGroup[$assignment->groupID])) {
				$usersToGroup[$assignment->groupID] = [];
			}
			
			$newUsers = UserGroupAssignmentHandler::getInstance()->getUsers($assignment, self::MAXIMUM_ASSIGNMENTS);
			$usersToGroup[$assignment->groupID] = array_merge($usersToGroup[$assignment->groupID], $newUsers);
			
			$assignmentCount += count($newUsers);
			if ($assignmentCount > self::MAXIMUM_ASSIGNMENTS) {
				break;
			}
		}
		
		foreach ($usersToGroup as $groupID => $users) {
			if (!empty($users)) {
				$userAction = new UserAction(array_unique($users), 'addToGroups', [
					'addDefaultGroups' => false,
					'deleteOldGroups' => false,
					'groups' => [$groupID]
				]);
				$userAction->executeAction();
			}
		}
	}
}
