<?php

namespace wcf\system\user\notification\event;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\email\Email;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\ModerationQueueUserNotificationObject;
use wcf\system\WCF;

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
final class ReportModerationQueueUserNotificationEvent extends AbstractUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableModerationQueueUserNotificationEvent;
    use TTestableUserNotificationEvent;

    private ViewableModerationQueue $viewableModerationQueue;
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
                'moderationQueue' => $this->getViewableModerationQueue(),
            ]
        );
    }

    #[\Override]
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'com.woltlab.wcf.moderation.queue.notification/'
                . $this->getUserNotificationObject()->queueID,
            'template' => 'email_notification_moderationQueueReport',
            'application' => 'wcf',
            'references' => [
                '<com.woltlab.wcf.moderation.queue/'
                . $this->getUserNotificationObject()->queueID . '@' . Email::getHost() . '>',
            ],
            'variables' => [
                'author' => $this->author,
                'notification' => $this->notification,
                'moderationQueue' => $this->getViewableModerationQueue(),
            ],
        ];
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

    #[\Override]
    public function checkAccess()
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->getUserNotificationObject()->objectTypeID);
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IModerationQueueHandler);

        return $processor->isAffectedUser(
            $this->getUserNotificationObject()->getDecoratedObject(),
            WCF::getUser()->userID
        );
    }

    private function getViewableModerationQueue(): ViewableModerationQueue
    {
        if (!isset($this->viewableModerationQueue)) {
            $this->viewableModerationQueue = ViewableModerationQueue::getViewableModerationQueue(
                $this->getUserNotificationObject()->queueID
            );
        }
        return $this->viewableModerationQueue;
    }

    #[\Override]
    public static function canBeTriggeredByGuests()
    {
        return true;
    }

    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        return [new ModerationQueueUserNotificationObject(self::getTestUserModerationQueueEntry($recipient, $author))];
    }
}
