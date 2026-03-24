<?php

namespace wcf\system\user\notification\event;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\system\comment\CommentHandler;
use wcf\system\email\Email;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for moderation queue comments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  CommentUserNotificationObject   getUserNotificationObject()
 */
class ModerationQueueCommentUserNotificationEvent extends AbstractCommentUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentUserNotificationEvent;
    use TTestableModerationQueueUserNotificationEvent;

    /**
     * language item prefix for the notification texts
     * @var string
     */
    protected $languageItemPrefix = '';

    /**
     * language item for the type name
     */
    protected string $typeName;

    /**
     * moderation queue object the notifications (indirectly) belong to
     * @var ViewableModerationQueue
     */
    protected $moderationQueue;

    #[\Override]
    public function checkAccess()
    {
        if (!WCF::getSession()->getPermission('mod.general.canUseModeration')) {
            return false;
        }
        if (!$this->moderationQueue->queueID) {
            return false;
        }

        return $this->moderationQueue->canEdit();
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): array
    {
        return [
            'message-id' => 'com.woltlab.wcf.moderation.queue.notification/' . $this->getUserNotificationObject()->commentID,
            'template' => 'email_notification_moderationQueueComment',
            'application' => 'wcf',
            'references' => [
                '<com.woltlab.wcf.moderation.queue/' . $this->moderationQueue->queueID . '@' . Email::getHost() . '>',
            ],
            'variables' => [
                'moderationQueue' => $this->moderationQueue,
                'languageItemPrefix' => $this->languageItemPrefix,
            ],
        ];
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->moderationQueue->queueID);
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->moderationQueue->getLink() . '#comments';
    }

    #[\Override]
    public function getMessage()
    {
        $authors = $this->getAuthors();
        if (\count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = \count($authors);

            return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix . '.comment.message.stacked', [
                'author' => $this->author,
                'authors' => \array_values($authors),
                'count' => $count,
                'others' => $count - 1,
                'moderationQueue' => $this->moderationQueue,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable($this->languageItemPrefix . '.comment.message', [
            'author' => $this->author,
            'commentID' => $this->getUserNotificationObject()->commentID,
            'moderationQueue' => $this->moderationQueue,
        ]);
    }

    #[\Override]
    protected function prepare() {}

    #[\Override]
    public function setObject(
        UserNotification $notification,
        IUserNotificationObject $object,
        UserProfile $author,
        array $additionalData = []
    ) {
        parent::setObject($notification, $object, $author, $additionalData);

        $this->moderationQueue = new ViewableModerationQueue(
            new ModerationQueue($this->getUserNotificationObject()->objectID)
        );
        if (!$this->moderationQueue->queueID) {
            return;
        }

        /** @var IModerationQueueHandler $moderationHandler */
        $moderationHandler = ObjectTypeCache::getInstance()
            ->getObjectType($this->moderationQueue->objectTypeID)
            ->getProcessor();
        $this->languageItemPrefix = $moderationHandler->getCommentNotificationLanguageItemPrefix();
        $this->typeName = $this->getLanguage()->get($moderationHandler->getCommentNotificationTypeNameLanguageItem());
    }

    #[\Override]
    public static function canBeTriggeredByGuests()
    {
        return false;
    }

    /**
     * @return array{objectID: int, objectTypeID: ?int}
     */
    protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
    {
        return [
            'objectID' => self::getTestUserModerationQueueEntry($author, $recipient)->queueID,
            'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue'),
        ];
    }

    #[\Override]
    protected function getTypeName(): string
    {
        return $this->typeName;
    }

    #[\Override]
    protected function getObjectTitle(): string
    {
        return $this->moderationQueue->getTitle();
    }
}
