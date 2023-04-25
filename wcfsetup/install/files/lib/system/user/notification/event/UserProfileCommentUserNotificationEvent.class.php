<?php

namespace wcf\system\user\notification\event;

use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * User notification event for profile comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  CommentUserNotificationObject   getUserNotificationObject()
 */
class UserProfileCommentUserNotificationEvent extends AbstractCommentUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->objectID);
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

            return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message.stacked', [
                'author' => $this->author,
                'authors' => \array_values($authors),
                'commentID' => $this->getUserNotificationObject()->commentID,
                'count' => $count,
                'others' => $count - 1,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message', [
            'author' => $this->author,
            'commentID' => $this->getUserNotificationObject()->commentID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'com.woltlab.wcf.user.profileComment.notification/' . $this->getUserNotificationObject()->commentID,
            'template' => 'email_notification_comment',
            'application' => 'wcf',
            'variables' => [
                'commentID' => $this->getUserNotificationObject()->commentID,
                'owner' => UserProfileRuntimeCache::getInstance()
                    ->getObject($this->getUserNotificationObject()->objectID),
                'languageVariablePrefix' => 'wcf.user.notification.comment',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return UserProfileRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->objectID)->getLink()
            . '#wall/comment' . $this->getUserNotificationObject()->commentID;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeName(): string
    {
        return $this->getLanguage()->get('wcf.user.profile.menu.wall');
    }

    /**
     * @inheritDoc
     */
    protected function getObjectTitle(): string
    {
        return UserProfileRuntimeCache::getInstance()
            ->getObject($this->getUserNotificationObject()->objectID)->username;
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
    {
        return [
            'objectID' => $recipient->userID,
            'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment'),
        ];
    }
}
