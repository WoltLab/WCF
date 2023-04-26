<?php

namespace wcf\system\user\notification\event;

use wcf\data\page\PageCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\email\Email;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * User notification event for page comments.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 *
 * @method  CommentResponseUserNotificationObject   getUserNotificationObject()
 */
class PageCommentResponseUserNotificationEvent extends AbstractCommentResponseUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentResponseUserNotificationEvent;
    use TTestablePageUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
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
                'wcf.user.notification.pageComment.response.message.stacked',
                [
                    'author' => $this->author,
                    'authors' => \array_values($authors),
                    'commentID' => $this->getUserNotificationObject()->commentID,
                    'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
                    'count' => $count,
                    'others' => $count - 1,
                    'guestTimesTriggered' => $this->notification->guestTimesTriggered,
                    'commentAuthor' => $this->getCommentAuthorProfile(),
                ]
            );
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.pageComment.response.message', [
            'author' => $this->author,
            'commentID' => $this->getUserNotificationObject()->commentID,
            'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
            'responseID' => $this->getUserNotificationObject()->responseID,
            'commentAuthor' => $this->getCommentAuthorProfile(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $messageID = '<com.woltlab.wcf.user.pageComment.notification/' . $this->getUserNotificationObject()->commentID . '@' . Email::getHost() . '>';

        return [
            'template' => 'email_notification_commentResponse',
            'in-reply-to' => [$messageID],
            'references' => [$messageID],
            'application' => 'wcf',
            'variables' => [
                'commentID' => $this->getUserNotificationObject()->commentID,
                'page' => PageCache::getInstance()->getPage($this->additionalData['objectID']),
                'languageVariablePrefix' => 'wcf.user.notification.pageComment.response',
                'responseID' => $this->getUserNotificationObject()->responseID,
                'commentAuthor' => $this->getCommentAuthorProfile(),
            ],
        ];
    }

    /**
     * Returns the comment authors profile.
     *
     * @return      UserProfile
     */
    private function getCommentAuthorProfile()
    {
        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);

        if ($comment->userID) {
            return UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
        } else {
            return UserProfile::getGuestUserProfile($comment->username);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return PageCache::getInstance()->getPage($this->additionalData['objectID'])->getLink() . '#comment' . $this->getUserNotificationObject()->commentID;
    }

    /**
     * @inheritDoc
     */
    protected function getTypeName(): string
    {
        return $this->getLanguage()->get('wcf.search.object.com.woltlab.wcf.page');
    }

    /**
     * @inheritDoc
     */
    protected function getObjectTitle(): string
    {
        return PageCache::getInstance()->getPage($this->additionalData['objectID'])->getTitle();
    }

    /**
     * @inheritDoc
     */
    protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
    {
        return [
            'objectID' => self::getTestPage()->pageID,
            'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.page'),
        ];
    }
}
