<?php

namespace wcf\system\user\notification\event;

use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for profile comment response likes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LikeUserNotificationObject  getUserNotificationObject()
 */
class UserProfileCommentResponseLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentResponseLikeUserNotificationEvent;
    use TReactionUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected $stackable = true;

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
        UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.commentResponse.like.title.stacked',
                [
                    'count' => $count,
                    'timesTriggered' => $this->notification->timesTriggered,
                ]
            );
        }

        return $this->getLanguage()->get('wcf.user.notification.commentResponse.like.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);
        $commentUser = $owner = null;
        if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
            $owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
        }
        if ($this->additionalData['commentUserID'] != WCF::getUser()->userID) {
            $commentUser = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['commentUserID']);
        }

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.commentResponse.like.message.stacked',
                [
                    'authorList' => $this->getLanguage()->getDynamicVariable('wcf.user.notification.stacked.authorList', [
                        'authors' => $authors,
                        'count' => $count,
                        'others' => $count - 1,
                    ]),
                    'owner' => $owner,
                    'reaction' => $this->getSingleReaction(),
                    // Not used anymore but kept here for backwards compatibility with third party translations
                    'author' => $this->author,
                    'authors' => $authors,
                    'commentID' => $this->additionalData['commentID'],
                    'commentUser' => $commentUser,
                    'count' => $count,
                    'others' => $count - 1,
                    'responseID' => $this->getResponseID(),
                    'reactions' => $this->getReactionsForAuthors(),
                ]
            );
        }

        return $this->getLanguage()->getDynamicVariable(
            'wcf.user.notification.commentResponse.like.message',
            [
                'author' => $this->author,
                'owner' => $owner,
                'reaction' => $this->getSingleReaction(),
                // Not used anymore but kept here for backwards compatibility with third party translations
                'commentID' => $this->additionalData['commentID'],
                'responseID' => $this->getResponseID(),
                'userNotificationObject' => $this->getUserNotificationObject(),
                'reactions' => $this->getReactionsForAuthors(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        throw new \LogicException('Unreachable');
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        $owner = WCF::getUser();
        if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
            $owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
        }

        return $owner->getLink() . '#wall/comment' . $this->additionalData['commentID'] . '/response' . $this->getResponseID();
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
    }

    /**
     * @inheritDoc
     */
    public function supportsEmailNotification()
    {
        return false;
    }

    /**
     * Returns the liked response's id.
     *
     * @return      int
     */
    protected function getResponseID()
    {
        // this is the `wcfN_like.objectID` value
        return $this->getUserNotificationObject()->objectID;
    }

    /**
     * @inheritDoc
     * @return array{objectID: int, objectTypeID: ?int}
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
