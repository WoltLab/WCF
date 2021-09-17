<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\article\content\ArticleContent;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;

/**
 * Represents a comment notification object type for comments on articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since       5.2
 */
class ArticleCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements
    ICommentUserNotificationObjectType,
    IMultiRecipientCommentUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = CommentUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = Comment::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = CommentList::class;

    /**
     * @inheritDoc
     */
    public function getOwnerID($objectID)
    {
        $sql = "SELECT      article.userID
                FROM        wcf" . WCF_N . "_comment comment
                LEFT JOIN   wcf" . WCF_N . "_article article
                ON          article.articleID = comment.objectID
                WHERE       comment.commentID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$objectID]);

        return $statement->fetchSingleColumn() ?: 0;
    }

    /**
     * @inheritDoc
     */
    public function getRecipientIDs(Comment $comment)
    {
        $articleContent = new ArticleContent($comment->objectID);
        $article = $articleContent->getArticle();

        \assert($article->articleID !== 0);

        $subscribers = $article->getCategory()->getSubscribedUserIDs();

        $users = UserProfileRuntimeCache::getInstance()->getObjects($subscribers);

        // Add the article author to the recipients, to ensure, that he
        // receive a notifications, even if he has not subscribed the category.
        $recipients = [$article->getUserID()];
        foreach ($users as $user) {
            if ($article->canRead($user)) {
                $recipients[] = $user->userID;
            }
        }

        return \array_unique($recipients);
    }
}
