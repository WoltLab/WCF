<?php

namespace wcf\command\moderation\queue;

use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Marks all moderation queues as read for the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkAllModerationQueuesAsRead
{
    public function __invoke(): void
    {
        VisitTracker::getInstance()->trackTypeVisit('com.woltlab.wcf.moderation.queue');

        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');
    }
}
