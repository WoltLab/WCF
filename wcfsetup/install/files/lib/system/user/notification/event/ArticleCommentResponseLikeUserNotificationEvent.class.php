<?php

namespace wcf\system\user\notification\event;

use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\cache\runtime\ViewableArticleContentRuntimeCache;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\WCF;

/**
 * User notification event for article comment response likes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license WoltLab License <http://www.woltlab.com/license-agreement.html>
 * @package WoltLabSuite\Core\System\User\Notification\Event
 * @since 5.5
 *
 * @method  LikeUserNotificationObject  getUserNotificationObject()
 */
class ArticleCommentResponseLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentResponseLikeUserNotificationEvent;
    use TTestableArticleCommentUserNotificationEvent;
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
        ViewableArticleContentRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
        UserRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.articleComment.response.like.title.stacked',
                [
                    'count' => $count,
                    'timesTriggered' => $this->notification->timesTriggered,
                ]
            );
        }

        return $this->getLanguage()->get('wcf.user.notification.articleComment.response.like.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $article = ViewableArticleContentRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);
        $commentUser = null;
        if ($this->additionalData['commentUserID'] != WCF::getUser()->userID) {
            $commentUser = UserRuntimeCache::getInstance()->getObject($this->additionalData['commentUserID']);
        }

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.articleComment.response.like.message.stacked',
                [
                    'author' => $this->author,
                    'authors' => $authors,
                    'commentID' => $this->additionalData['commentID'],
                    'commentUser' => $commentUser,
                    'count' => $count,
                    'others' => $count - 1,
                    'article' => $article,
                    'responseID' => $this->getUserNotificationObject()->objectID,
                    'reactions' => $this->getReactionsForAuthors(),
                ]
            );
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.response.like.message', [
            'author' => $this->author,
            'commentID' => $this->additionalData['commentID'],
            'article' => $article,
            'responseID' => $this->getUserNotificationObject()->objectID,
            'reactions' => $this->getReactionsForAuthors(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        // not supported
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return ViewableArticleContentRuntimeCache::getInstance()->getObject($this->additionalData['objectID'])->getLink()
            . '#comment' . $this->additionalData['commentID'] . '/response' . $this->getUserNotificationObject()->objectID;
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
}
