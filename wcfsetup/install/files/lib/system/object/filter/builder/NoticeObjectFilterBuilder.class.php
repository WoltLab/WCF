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
 * @implements IObjectFilterBuilder<User>
 */
final class NoticeObjectFilterBuilder implements IObjectFilterBuilder
{
    /**
     * @var list<IObjectFilter<User, mixed>>
     */
    private readonly array $filters;

    /**
     * @var ObjectFilterHandler<User>
     */
    private ObjectFilterHandler $handler;

    public function __construct()
    {
        $this->filters = [
            new UserAvatarObjectFilter(),
            new UserLanguageObjectFilter(),
        ];
    }

    #[\Override]
    public function getFilters(): array
    {
        return $this->filters;
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

    /**
     * @return ObjectFilterHandler<User>
     */
    private function getHandler(): ObjectFilterHandler
    {
        if (!isset($this->handler)) {
            $this->handler = new ObjectFilterHandler($this->getFilters());
        }

        return $this->handler;
    }
}
