<?php

namespace wcf\system\object\filter\builder;

use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\object\filter\ObjectListFilterHandler;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;
use wcf\system\WCF;

/**
 * @implements IObjectFilterBuilder<User>
 */
final class UserGroupAssignmentObjectFilterBuilder implements IObjectFilterBuilder
{
    /**
     * @var list<IObjectListFilter<User, mixed>>
     */
    private readonly array $filters;

    /**
     * @var ObjectListFilterHandler<User>
     */
    private ObjectListFilterHandler $handler;

    public function __construct()
    {
        $this->filters = [
            new UserAvatarObjectFilter(),
            new UserLanguageObjectFilter(),
        ];
    }

    /**
     * @return list<IObjectListFilter<User, mixed>>
     */
    #[\Override]
    public function getFilters(): array
    {
        return $this->filters;
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userGroupAssignment';
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

    /**
     * @return ObjectListFilterHandler<User>
     */
    private function getHandler(): ObjectListFilterHandler
    {
        if (!isset($this->handler)) {
            $this->handler = new ObjectListFilterHandler($this->getFilters());
        }

        return $this->handler;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canManageGroupAssignment');
    }
}
