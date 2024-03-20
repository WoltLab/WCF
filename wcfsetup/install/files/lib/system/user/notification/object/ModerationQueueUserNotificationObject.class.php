<?php

namespace wcf\system\user\notification\object;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\moderation\queue\ModerationQueue;

/**
 * Notification object for moderation queue.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @method  ModerationQueue     getDecoratedObject()
 * @mixin   ModerationQueue
 */
final class ModerationQueueUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModerationQueue::class;

    #[\Override]
    public function getTitle(): string
    {
        return '';
    }

    #[\Override]
    public function getURL()
    {
        return '';
    }

    #[\Override]
    public function getAuthorID()
    {
        return $this->userID;
    }
}
