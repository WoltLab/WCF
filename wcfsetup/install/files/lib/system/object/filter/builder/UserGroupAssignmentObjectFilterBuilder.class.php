<?php

namespace wcf\system\object\filter\builder;

use Override;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\ObjectFilterHandler;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;

final class UserGroupAssignmentObjectFilterBuilder implements IObjectFilterBuilder
{
    /**
     * @var list<IObjectFilter<mixed>>
     */
    private readonly array $filters;

    private ObjectFilterHandler $handler;

    public function __construct()
    {
        $this->filters = [
            new UserAvatarObjectFilter(),
            new UserLanguageObjectFilter(),
        ];
    }

    #[Override]
    public function getFilters(): array
    {
        return $this->filters;
    }

    #[Override]
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.userGroupAssignment';
    }

    public function applyFilters(UserGroupAssignment $assignment, PreparedStatementConditionBuilder $conditions): void
    {
        $this->getHandler()->applyFilters(
            $conditions,
            $this->getObjectTypeName(),
            $assignment->conditions,
        );
    }

    public function testUser(UserGroupAssignment $assignment, User $user): bool
    {
        return $this->getHandler()->testUser(
            $user,
            $this->getObjectTypeName(),
            $assignment->conditions,
        );
    }

    private function getHandler(): ObjectFilterHandler
    {
        if (!isset($this->handler)) {
            $this->handler = new ObjectFilterHandler($this->getFilters());
        }

        return $this->handler;
    }
}
