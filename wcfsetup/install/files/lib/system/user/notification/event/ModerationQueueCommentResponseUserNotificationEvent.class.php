<?php

namespace wcf\system\user\notification\event;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\email\Email;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for moderation queue comments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  CommentResponseUserNotificationObject   getUserNotificationObject()
 */
class ModerationQueueCommentResponseUserNotificationEvent extends AbstractCommentResponseUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentResponseUserNotificationEvent;
    use TTestableModerationQueueUserNotificationEvent;

    /**
     * language item prefix for the notification texts
     * @var string
     */
    protected $languageItemPrefix;

    /**
     * moderation queue object the notifications (indirectly) belong to
     * @var ViewableModerationQueue
     */
    protected $moderationQueue;

    /**
     * true if the moderation queue is already loaded
     * @var bool
     */
    protected $moderationQueueLoaded = false;

    /**
     * language item for the type name
     */
    protected string $typeName;

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        if (!WCF::getSession()->getPermission('mod.general.canUseModeration')) {
            return false;
        }

        return $this->getModerationQueue()->canEdit();
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
        if ($comment->userID) {
            $commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
        } else {
            $commentAuthor = UserProfile::getGuestUserProfile($comment->username);
        }

        $messageID = '<com.woltlab.wcf.moderation.queue.notification/' . $comment->commentID . '@' . Email::getHost() . '>';

        return [
            'template' => 'email_notification_moderationQueueCommentResponse',
            'application' => 'wcf',
            'in-reply-to' => [$messageID],
            'references' => [
                '<com.woltlab.wcf.moderation.queue/' . $this->getModerationQueue()->queueID . '@' . Email::getHost() . '>',
                $messageID,
            ],
            'variables' => [
                'moderationQueue' => $this->getModerationQueue(),
                'commentAuthor' => $commentAuthor,
                'languageItemPrefix' => $this->getLanguageItemPrefix(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getModerationQueue()->queueID);
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return $this->getModerationQueue()->getLink() . '#comment' . $this->getUserNotificationObject()->commentID;
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $authors = $this->getAuthors();
        if (\count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = \count($authors);

            return $this->getLanguage()->getDynamicVariable(
                $this->getLanguageItemPrefix() . '.commentResponse.message.stacked',
                [
                    'authors' => \array_values($authors),
                    'commentID' => $this->getUserNotificationObject()->commentID,
                    'count' => $count,
                    'others' => $count - 1,
                    'moderationQueue' => $this->getModerationQueue(),
                ]
            );
        }

        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
        if ($comment->userID) {
            $commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
        } else {
            $commentAuthor = UserProfile::getGuestUserProfile($comment->username);
        }

        return $this->getLanguage()->getDynamicVariable($this->getLanguageItemPrefix() . '.commentResponse.message', [
            'author' => $this->author,
            'commentAuthor' => $commentAuthor,
            'commentID' => $this->getUserNotificationObject()->commentID,
            'responseID' => $this->getUserNotificationObject()->responseID,
            'moderationQueue' => $this->getModerationQueue(),
        ]);
    }

    /**
     * Returns the moderation queue object the responded to comment belongs to.
     * Returns null if the active user has no access to the moderation queue.
     *
     * @return  ViewableModerationQueue
     */
    public function getModerationQueue()
    {
        if (!$this->moderationQueueLoaded) {
            $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);

            $this->moderationQueue = new ViewableModerationQueue(new ModerationQueue($comment->objectID));
            $this->moderationQueueLoaded = true;
        }

        return $this->moderationQueue;
    }

    /**
     * Returns the language item prefix for the notification texts.
     *
     * @return  string
     */
    public function getLanguageItemPrefix()
    {
        if ($this->languageItemPrefix === null) {
            /** @var IModerationQueueReportHandler $moderationHandler */
            $moderationHandler = ObjectTypeCache::getInstance()
                ->getObjectType($this->getModerationQueue()->objectTypeID)
                ->getProcessor();
            $this->languageItemPrefix = $moderationHandler->getCommentNotificationLanguageItemPrefix();
        }

        return $this->languageItemPrefix;
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function canBeTriggeredByGuests()
    {
        return false;
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
    {
        return [
            'objectID' => self::getTestUserModerationQueueEntry($author, $recipient)->queueID,
            'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getTypeName(): string
    {
        if (!isset($this->typeName)) {
            $moderationHandler = ObjectTypeCache::getInstance()
                ->getObjectType($this->getModerationQueue()->objectTypeID)
                ->getProcessor();
            $this->typeName = $this->getLanguage()->get($moderationHandler->getCommentNotificationTypeNameLanguageItem());
        }

        return $this->typeName;
    }

    /**
     * @inheritDoc
     */
    protected function getObjectTitle(): string
    {
        return $this->moderationQueue->getTitle();
    }
}
