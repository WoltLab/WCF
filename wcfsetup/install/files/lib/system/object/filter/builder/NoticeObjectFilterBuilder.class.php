<?php

namespace wcf\system\object\filter\builder;

use wcf\data\notice\Notice;
use wcf\data\user\User;
use wcf\event\notice\NoticeObjectFilterCollecting;
use wcf\system\event\EventHandler;
use wcf\system\object\filter\date\DaysOfWeekObjectFilter;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\object\filter\ObjectFilterHandler;
use wcf\system\object\filter\page\NotRequestedPageObjectFilter;
use wcf\system\object\filter\page\RequestedPageObjectFilter;
use wcf\system\object\filter\user\UserActivatedObjectFilter;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserBannedObjectFilter;
use wcf\system\object\filter\user\UserBirthdayObjectFilter;
use wcf\system\object\filter\user\UserEmailConfirmedObjectFilter;
use wcf\system\object\filter\user\UserEmailObjectFilter;
use wcf\system\object\filter\user\UserGroupObjectFilter;
use wcf\system\object\filter\user\UserIntegerPropertyObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;
use wcf\system\object\filter\user\UserMobileBrowserObjectFilter;
use wcf\system\object\filter\user\UserMultifactorObjectFilter;
use wcf\system\object\filter\user\UserNotInGroupObjectFilter;
use wcf\system\object\filter\user\UserNoTrophyObjectFilter;
use wcf\system\object\filter\user\UserRegistrationDateObjectFilter;
use wcf\system\object\filter\user\UserRegistrationDaysObjectFilter;
use wcf\system\object\filter\user\UserTrophyObjectFilter;
use wcf\system\object\filter\user\UserUsernameObjectFilter;
use wcf\system\WCF;

/**
 * Provides the filters for the conditions of notices, controlling which users
 * a notice is shown to.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractObjectFilterBuilder<User, IObjectFilter<User, mixed>, ObjectFilterHandler<User>>
 */
final class NoticeObjectFilterBuilder extends AbstractObjectFilterBuilder
{
    #[\Override]
    protected function createFilters(): array
    {
        $event = new NoticeObjectFilterCollecting();
        $event->register(new RequestedPageObjectFilter());
        $event->register(new NotRequestedPageObjectFilter());
        $event->register(new DaysOfWeekObjectFilter());
        $event->register(new UserUsernameObjectFilter());
        $event->register(new UserEmailObjectFilter());
        $event->register(new UserGroupObjectFilter(includeGuests: true));
        $event->register(new UserNotInGroupObjectFilter(includeGuests: true));
        $event->register(new UserLanguageObjectFilter());
        $event->register(new UserRegistrationDateObjectFilter());
        $event->register(new UserRegistrationDaysObjectFilter());
        $event->register(new UserAvatarObjectFilter());
        $event->register(new UserBannedObjectFilter());
        $event->register(new UserActivatedObjectFilter());
        $event->register(new UserEmailConfirmedObjectFilter());
        $event->register(new UserMultifactorObjectFilter());
        $event->register(new UserBirthdayObjectFilter());
        $event->register(new UserMobileBrowserObjectFilter());
        $event->register(new UserIntegerPropertyObjectFilter('activityPoints', 'wcf.user.condition.activityPoints'));
        $event->register(new UserIntegerPropertyObjectFilter('likesReceived', 'wcf.user.condition.likesReceived'));
        $event->register(new UserIntegerPropertyObjectFilter('trophyPoints', 'wcf.user.condition.trophyPoints'));
        $event->register(new UserTrophyObjectFilter());
        $event->register(new UserNoTrophyObjectFilter());

        EventHandler::getInstance()->fire($event);

        return $event->getFilters();
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
