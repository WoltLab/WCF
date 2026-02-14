<?php

namespace wcf\system\user\notification\event;

use wcf\system\cache\runtime\ViewableArticleContentRuntimeCache;
use wcf\system\user\notification\object\LikeUserNotificationObject;

/**
 * User notification event for article comment likes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 *
 * @method  LikeUserNotificationObject  getUserNotificationObject()
 */
class ArticleCommentLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableCommentLikeUserNotificationEvent;
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
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.like.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->get('wcf.user.notification.articleComment.like.title');
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $article = ViewableArticleContentRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.like.message.stacked', [
                'authorList' => $this->getLanguage()->getDynamicVariable('wcf.user.notification.stacked.authorList', [
                    'authors' => $authors,
                    'count' => $count,
                    'others' => $count - 1,
                ]),
                'article' => $article,
                'reaction' => $this->getSingleReaction(),
                // Not used anymore but kept here for backwards compatibility with third party translations
                'author' => $this->author,
                'authors' => $authors,
                'commentID' => $this->getCommentID(),
                'count' => $count,
                'others' => $count - 1,
                'reactions' => $this->getReactionsForAuthors(),
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.like.message', [
            'author' => $this->author,
            'article' => $article,
            'reaction' => $this->getSingleReaction(),
            // Not used anymore but kept here for backwards compatibility with third party translations
            'commentID' => $this->getCommentID(),
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
    public function getLink(): string
    {
        return ViewableArticleContentRuntimeCache::getInstance()->getObject($this->additionalData['objectID'])->getLink() . '#comment' . $this->getCommentID();
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getCommentID());
    }

    /**
     * @inheritDoc
     */
    public function supportsEmailNotification()
    {
        return false;
    }

    /**
     * Returns the liked comment's id.
     *
     * @return      int
     */
    protected function getCommentID()
    {
        // this is the `wcfN_like.objectID` value
        return $this->getUserNotificationObject()->objectID;
    }
}
