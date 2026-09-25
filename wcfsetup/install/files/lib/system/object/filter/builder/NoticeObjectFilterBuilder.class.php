<?php

namespace wcf\system\object\filter\builder;

use wcf\data\notice\Notice;
use wcf\data\user\User;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\ObjectFilterHandler;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;
use wcf\system\WCF;

/**
 * @extends AbstractObjectFilterBuilder<User, IObjectFilter<User, mixed>, ObjectFilterHandler<User>>
 */
final class NoticeObjectFilterBuilder extends AbstractObjectFilterBuilder
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
        return new ObjectFilterHandler($filters);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.notice';
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.notice.canManageNotice');
    }

    /**
     * Returns whether the given user matches the filters of the notice.
     *
     * A notice without any filters is visible to everyone.
     */
    public function testUser(Notice $notice, User $user): bool
    {
        if ($notice->conditions === null) {
            return true;
        }

        return $this->getHandler()->testObject($user, $notice->conditions);
    }
}
