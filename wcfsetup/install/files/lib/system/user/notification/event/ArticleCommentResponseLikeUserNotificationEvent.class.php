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
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

    #[\Override]
    protected function prepare()
    {
        ViewableArticleContentRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
        UserRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
    }

    #[\Override]
    public function getTitle(): string
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

    #[\Override]
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
                    'commentID' => $this->additionalData['commentID'],
                    'commentUser' => $commentUser,
                    'count' => $count,
                    'others' => $count - 1,
                    'responseID' => $this->getUserNotificationObject()->objectID,
                    'reactions' => $this->getReactionsForAuthors(),
                ]
            );
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.articleComment.response.like.message', [
            'author' => $this->author,
            'article' => $article,
            'reaction' => $this->getSingleReaction(),
            // Not used anymore but kept here for backwards compatibility with third party translations
            'commentID' => $this->additionalData['commentID'],
            'responseID' => $this->getUserNotificationObject()->objectID,
            'reactions' => $this->getReactionsForAuthors(),
        ]);
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): string
    {
        // not supported
        return '';
    }

    #[\Override]
    public function getLink(): string
    {
        return ViewableArticleContentRuntimeCache::getInstance()->getObject($this->additionalData['objectID'])->getLink()
            . '#comment' . $this->additionalData['commentID'] . '/response' . $this->getUserNotificationObject()->objectID;
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
    }

    #[\Override]
    public function supportsEmailNotification()
    {
        return false;
    }
}
