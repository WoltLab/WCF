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
    #[\Override]
    public function getTitle(): string
    {
        return $this->getLanguage()->get('wcf.moderation.report.notification.title');
    }

    #[\Override]
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable(
            'wcf.moderation.report.notification.message',
            [
                'author' => $this->author,
                'notification' => $this->notification,
                'userNotificationObject' => $this->getUserNotificationObject(),
            ]
        );
    }

    #[\Override]
    public function getEmailMessage($notificationType = 'instant')
    {
        // TODO
    }

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('ModerationReport', [
            'id' => $this->getUserNotificationObject()->queueID,
        ]);
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->queueID);
    }
}
