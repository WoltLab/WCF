<?php

namespace wcf\system\user\notification\event;

use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\ModerationQueueUserNotificationObject;

/**
 * Notification event for new reports in the moderation queue.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @method  ModerationQueueUserNotificationObject    getUserNotificationObject()
 */
final class ReportModerationQueueUserNotificationEvent extends AbstractUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        // TODO
        return "";
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        // TODO
        return "";
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        // TODO
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('ModerationReport', [
            'id' => $this->getUserNotificationObject()->queueID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->queueID);
    }
}
