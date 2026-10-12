<?php

namespace wcf\acp\form;

use wcf\data\user\User;
use wcf\event\user\UsersMerging;
use wcf\system\event\EventHandler;

/**
 * Provides the legacy `save` event of the former user merge form for event
 * listeners that are registered for this class.
 *
 * The constructor is private to prevent this class from being resolved as a
 * controller.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Listen for the `UsersMerging` event instead.
 */
final class UserMergeForm
{
    /**
     * ids of the relevant users
     * @var int[]
     */
    public array $userIDs;

    /**
     * ids of merge users (without destination user)
     * @var int[]
     */
    public array $mergedUserIDs;

    /**
     * destination user id
     */
    public int $destinationUserID;

    /**
     * relevant users
     * @var array<int, User>
     */
    public array $users = [];

    private function __construct(UsersMerging $event)
    {
        $this->destinationUserID = $event->destinationUser->userID;
        $this->users[$this->destinationUserID] = $event->destinationUser;
        foreach ($event->mergedUsers as $user) {
            $this->users[$user->userID] = $user;
        }

        $this->userIDs = \array_keys($this->users);
        $this->mergedUserIDs = \array_values(\array_map(
            static fn(User $user) => $user->userID,
            $event->mergedUsers
        ));
    }

    /**
     * Fires the legacy `save` event.
     */
    public static function fireLegacyEvent(UsersMerging $event): void
    {
        EventHandler::getInstance()->fireAction(new self($event), 'save');
    }
}
