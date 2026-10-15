<?php

namespace wcf\command\user\notification\event;

use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\event\UserNotificationEventBuilder;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\WCF;

/**
 * Sets the default notification settings of a user notification event that
 * apply to new users and, optionally, to all existing users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetUserNotificationEventPreset
{
    /**
     * @param 'none'|'instant'|'daily' $presetMailNotificationType
     */
    public function __construct(
        private readonly UserNotificationEvent $event,
        private readonly bool $preset,
        private readonly string $presetMailNotificationType,
        private readonly bool $applyToExistingUsers = false,
    ) {}

    public function __invoke(): UserNotificationEvent
    {
        $event = UserNotificationEventBuilder::forUpdate($this->event)
            ->setPreset($this->preset)
            ->setPresetMailNotificationType($this->presetMailNotificationType)
            ->update();

        if ($this->applyToExistingUsers) {
            $this->applyToExistingUsers($event);
        }

        UserNotificationEventCacheBuilder::getInstance()->reset();

        return $event;
    }

    private function applyToExistingUsers(UserNotificationEvent $event): void
    {
        if ($event->preset === 0) {
            $sql = "DELETE FROM wcf1_user_notification_event_to_user
                    WHERE       eventID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$event->eventID]);
        } else {
            $sql = "REPLACE INTO    wcf1_user_notification_event_to_user
                                    (userID, eventID, mailNotificationType)
                    SELECT          userID, ?, ?
                    FROM            wcf1_user";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$event->eventID, $event->presetMailNotificationType]);
        }
    }
}
