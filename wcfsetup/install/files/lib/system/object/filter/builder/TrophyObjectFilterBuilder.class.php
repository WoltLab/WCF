<?php

namespace wcf\system\object\filter\builder;

use wcf\data\trophy\Trophy;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\event\trophy\TrophyObjectFilterCollecting;
use wcf\system\event\EventHandler;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\object\filter\ObjectFilterHandler;
use wcf\system\object\filter\ObjectListFilterHandler;
use wcf\system\object\filter\user\UserActivatedObjectFilter;
use wcf\system\object\filter\user\UserAvatarObjectFilter;
use wcf\system\object\filter\user\UserBannedObjectFilter;
use wcf\system\object\filter\user\UserCoverPhotoObjectFilter;
use wcf\system\object\filter\user\UserEmailConfirmedObjectFilter;
use wcf\system\object\filter\user\UserEmailObjectFilter;
use wcf\system\object\filter\user\UserGroupObjectFilter;
use wcf\system\object\filter\user\UserIntegerPropertyObjectFilter;
use wcf\system\object\filter\user\UserLanguageObjectFilter;
use wcf\system\object\filter\user\UserNotInGroupObjectFilter;
use wcf\system\object\filter\user\UserNoTrophyObjectFilter;
use wcf\system\object\filter\user\UserOptionObjectFilterFactory;
use wcf\system\object\filter\user\UserRegistrationDateObjectFilter;
use wcf\system\object\filter\user\UserRegistrationDaysObjectFilter;
use wcf\system\object\filter\user\UserSignatureObjectFilter;
use wcf\system\object\filter\user\UserTrophyObjectFilter;
use wcf\system\object\filter\user\UserUsernameObjectFilter;
use wcf\system\WCF;

/**
 * Provides the filters for the conditions of trophies that are awarded
 * automatically.
 *
 * The filters are applied to the conditions of a user list to find all
 * matching users at once.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractObjectFilterBuilder<User, IObjectListFilter<User, mixed>, ObjectListFilterHandler<User>>
 */
final class TrophyObjectFilterBuilder extends AbstractObjectFilterBuilder
{
    #[\Override]
    protected function createFilters(): array
    {
        $event = new TrophyObjectFilterCollecting();
        $event->register(new UserUsernameObjectFilter());
        $event->register(new UserEmailObjectFilter());
        $event->register(new UserGroupObjectFilter());
        $event->register(new UserNotInGroupObjectFilter());
        $event->register(new UserLanguageObjectFilter());
        $event->register(new UserRegistrationDateObjectFilter());
        $event->register(new UserRegistrationDaysObjectFilter());
        $event->register(new UserAvatarObjectFilter());
        $event->register(new UserSignatureObjectFilter());
        $event->register(new UserCoverPhotoObjectFilter());
        $event->register(new UserBannedObjectFilter());
        $event->register(new UserActivatedObjectFilter());
        $event->register(new UserEmailConfirmedObjectFilter());
        $event->register(new UserIntegerPropertyObjectFilter('activityPoints', 'wcf.user.condition.activityPoints'));
        $event->register(new UserIntegerPropertyObjectFilter('likesReceived', 'wcf.user.condition.likesReceived'));
        $event->register(new UserIntegerPropertyObjectFilter('trophyPoints', 'wcf.user.condition.trophyPoints'));
        $event->register(new UserTrophyObjectFilter());
        $event->register(new UserNoTrophyObjectFilter());
        foreach ((new UserOptionObjectFilterFactory())->getFilters() as $filter) {
            $event->register($filter);
        }

        EventHandler::getInstance()->fire($event);

        return $event->getFilters();
    }

    #[\Override]
    protected function createHandler(array $filters): ObjectFilterHandler
    {
        return new ObjectListFilterHandler($filters);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.trophy';
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.trophy.canManageTrophy');
    }

    /**
     * Adds the filters of the trophy to the given user list. Returns whether
     * any active filter has been applied.
     *
     * A trophy without any active filters matches no users.
     */
    public function applyFilters(Trophy $trophy, UserList $userList): bool
    {
        return $this->getHandler()->applyFilters(
            $userList,
            $trophy->conditions,
        );
    }
}
