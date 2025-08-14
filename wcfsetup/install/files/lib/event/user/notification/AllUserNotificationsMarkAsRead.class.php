<?php

namespace wcf\event\user\notification;

use wcf\event\IPsr14Event;

/**
 * Indicates that all notifications of a user have been marked as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class AllUserNotificationsMarkAsRead implements IPsr14Event
{
    public function __construct(
        public readonly int $userID
    ) {}
}
