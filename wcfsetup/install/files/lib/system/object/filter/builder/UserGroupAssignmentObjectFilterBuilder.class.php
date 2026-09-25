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

    public function applyFilters(UserGroupAssignment $assignment, PreparedStatementConditionBuilder $conditions): void
    {
        $this->getHandler()->applyFilters(
            $conditions,
            $assignment->conditions,
        );
    }

    public function testUser(UserGroupAssignment $assignment, User $user): bool
    {
        return $this->getHandler()->testObject(
            $user,
            $assignment->conditions,
        );
    }
}
