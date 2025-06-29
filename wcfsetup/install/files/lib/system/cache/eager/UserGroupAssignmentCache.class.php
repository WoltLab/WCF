<?php

namespace wcf\system\cache\eager;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentList;

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
        $assignmentList = new UserGroupAssignmentList();
        $assignmentList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $assignmentList->getConditionBuilder()->add('isLegacy = ?', [0]);
        $assignmentList->readObjects();

        return $assignmentList->getObjects();
    }
}
