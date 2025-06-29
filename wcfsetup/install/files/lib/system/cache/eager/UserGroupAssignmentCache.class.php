<?php

namespace wcf\system\cache\eager;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentList;
use wcf\system\user\group\assignment\command\UserGroupAssignmentMigrateCondition;

/**
 * Caches the enabled automatic user group assignments.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<array<int, UserGroupAssignment>>
 */
final class UserGroupAssignmentCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $assignmentList = $this->getUserGroupAssignments();

        $migrationDone = false;
        foreach ($assignmentList as $assignment) {
            if ($assignment->isLegacy) {
                (new UserGroupAssignmentMigrateCondition($assignment))();
                $migrationDone = true;
            }
        }

        if ($migrationDone) {
            // Reload the list to ensure that no disabled assignments are included
            return $this->getUserGroupAssignments()->getObjects();
        } else {
            return $assignmentList->getObjects();
        }
    }

    private function getUserGroupAssignments(): UserGroupAssignmentList
    {
        $assignmentList = new UserGroupAssignmentList();
        $assignmentList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $assignmentList->readObjects();

        return $assignmentList;
    }
}
