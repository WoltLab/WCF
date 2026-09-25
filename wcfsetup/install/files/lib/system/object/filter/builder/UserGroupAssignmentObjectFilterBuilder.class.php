<?php

namespace wcf\system\object\filter\builder;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\object\filter\ObjectFilterHandler;
use wcf\system\object\filter\ObjectListFilterHandler;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;
use wcf\system\WCF;

/**
 * Provides the filters for the conditions of automatic user group assignments.
 *
 * The filters can be evaluated against a single user or applied to the
 * conditions of a user list to find all matching users at once.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractObjectFilterBuilder<User, IObjectListFilter<User, mixed>, ObjectListFilterHandler<User>>
 */
final class UserGroupAssignmentObjectFilterBuilder extends AbstractObjectFilterBuilder
{
    #[\Override]
    protected function createFilters(): array
    {
        return [
            new UserAvatarObjectFilter(),
            new UserLanguageObjectFilter(),
        ];
    }

    #[\Override]
    protected function createHandler(array $filters): ObjectFilterHandler
    {
        return new ObjectListFilterHandler($filters);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userGroupAssignment';
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canManageGroupAssignment');
    }

    /**
     * Adds the filters of the assignment to the given conditions of a user list.
     *
     * An assignment without any active filters matches no users.
     */
    public function applyFilters(UserGroupAssignment $assignment, PreparedStatementConditionBuilder $conditions): void
    {
        $this->getHandler()->applyFilters(
            $conditions,
            $assignment->conditions,
        );
    }

    /**
     * Returns whether the given user matches the filters of the assignment.
     *
     * An assignment without any active filters matches no users.
     */
    public function testUser(UserGroupAssignment $assignment, User $user): bool
    {
        return $this->getHandler()->testObject(
            $user,
            $assignment->conditions,
        );
    }
}
