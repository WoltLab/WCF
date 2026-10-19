<?php

namespace wcf\system\cronjob;

use wcf\command\user\AddUserToGroups;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;

/**
 * Executes automatic user group assignments and removes users who no longer
 * fulfill the assignment of user groups with automatic revocation.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupAssignmentCronjob extends AbstractCronjob
{
    const MAXIMUM_ASSIGNMENTS = 1000;

    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $assignmentCount = UserGroupAssignmentHandler::getInstance()->revokeUsers(self::MAXIMUM_ASSIGNMENTS);
        if ($assignmentCount >= self::MAXIMUM_ASSIGNMENTS) {
            return;
        }

        $assignments = UserGroupAssignmentCacheBuilder::getInstance()->getData();
        $usersToGroup = [];

        foreach ($assignments as $assignment) {
            if (!isset($usersToGroup[$assignment->groupID])) {
                $usersToGroup[$assignment->groupID] = [];
            }

            $newUsers = UserGroupAssignmentHandler::getInstance()->getUsers($assignment, self::MAXIMUM_ASSIGNMENTS);
            $usersToGroup[$assignment->groupID] = \array_merge($usersToGroup[$assignment->groupID], $newUsers);

            $assignmentCount += \count($newUsers);
            if ($assignmentCount > self::MAXIMUM_ASSIGNMENTS) {
                break;
            }
        }

        foreach ($usersToGroup as $groupID => $users) {
            foreach ($users as $user) {
                (new AddUserToGroups($user, [$groupID], ignoreUserGroupAssignments: true))();
            }
        }
    }
}
