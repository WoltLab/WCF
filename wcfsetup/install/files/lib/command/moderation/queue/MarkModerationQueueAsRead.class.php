<?php

namespace wcf\command\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Marks a moderation queue entry as read for the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkModerationQueueAsRead
{
    public function __construct(
        private readonly ModerationQueue $moderationQueue,
        private readonly int $visitTime = TIME_NOW
    ) {}

    public function __invoke(): void
    {
        VisitTracker::getInstance()->trackObjectVisit(
            'com.woltlab.wcf.moderation.queue',
            $this->moderationQueue->queueID,
            $this->visitTime
        );

        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');
    }
}
