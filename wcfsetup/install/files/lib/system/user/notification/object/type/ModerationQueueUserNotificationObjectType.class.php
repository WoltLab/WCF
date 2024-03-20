<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\system\user\notification\object\ModerationQueueUserNotificationObject;

/**
 * User notification object type implementation for moderation queue.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ModerationQueueUserNotificationObjectType extends AbstractUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = ModerationQueueUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = ModerationQueue::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = ModerationQueueList::class;
}
