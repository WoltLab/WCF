<?php

namespace wcf\system\comment\manager;

use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\data\article\content\ArticleContentList;
use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\ArticleContentRuntimeCache;
use wcf\system\cache\runtime\CommentResponseRuntimeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Article comment manager implementation.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleCommentManager extends AbstractCommentManager implements IViewableLikeProvider, ICommentPermissionManager
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.article.canAddComment';

    /**
     * @inheritDoc
     */
    protected $permissionAddWithoutModeration = 'user.article.canAddCommentWithoutModeration';

    /**
     * @inheritDoc
     */
    protected $permissionDelete = 'user.article.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionEdit = 'user.article.canEditComment';

    /**
     * @inheritDoc
     */
    protected $permissionModDelete = 'mod.article.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionModEdit = 'mod.article.canEditComment';

    /**
     * @inheritDoc
     */
    protected $permissionCanModerate = 'mod.article.canModerateComment';

    #[\Override]
    public function isAccessible(int $objectID, bool $validateWritePermission = false)
    {
        // check object id
        $articleContent = new ArticleContent($objectID);
        if ($articleContent->isNil() || !$articleContent->getArticle()->canRead()) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function canModerateObject(int $objectTypeID, int $objectID, UserProfile $user): bool
    {
        $articleContent = ArticleContentRuntimeCache::getInstance()->getObject($objectID);
        if ($articleContent === null) {
            return false;
        }
        if (!$articleContent->getArticle()->canRead($user)) {
            return false;
        }

        return $user->hasPermission($this->permissionCanModerate);
    }

    #[\Override]
    public function getLink(int $objectTypeID, int $objectID)
    {
        $articleContent = ArticleContentRuntimeCache::getInstance()->getObject($objectID);
        if ($articleContent !== null) {
            return $articleContent->getLink();
        }

        return '';
    }

    #[\Override]
    public function getTitle(int $objectTypeID, int $objectID, bool $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('wcf.article.commentResponse');
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.article.comment');
    }

    #[\Override]
    public function updateCounter(int $objectID, int $value)
    {
        $content = new ArticleContent($objectID);
        ArticleContentBuilder::forUpdate($content)
            ->incrementComments($value)
            ->update();
    }

    #[\Override]
    public function prepare(array $likes)
    {
        $commentLikeObjectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');

        $commentIDs = $responseIDs = [];
        foreach ($likes as $like) {
            if ($like->objectTypeID === $commentLikeObjectType->objectTypeID) {
                $commentIDs[] = $like->objectID;
            } else {
                $responseIDs[] = $like->objectID;
            }
        }

        // fetch response
        $responses = [];
        if ($responseIDs !== []) {
            $responses = CommentResponseRuntimeCache::getInstance()->getObjects($responseIDs);

            foreach ($responses as $response) {
                $commentIDs[] = $response->commentID;
            }
        }

        // fetch comments
        $comments = CommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // fetch users
        $articleContentIDs = [];
        foreach ($comments as $comment) {
            $articleContentIDs[] = $comment->objectID;
        }

        // fetch articles
        $articleContents = [];
        if ($articleContentIDs !== []) {
            $articleContentList = new ArticleContentList();
            $articleContentList->setObjectIDs($articleContentIDs);
            $articleContentList->readObjects();
            $articleContents = $articleContentList->getObjects();
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID === $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];

                    if (
                        isset($articleContents[$comment->objectID])
                        && $articleContents[$comment->objectID]->getArticle()->canRead()
                    ) {
                        $like->setIsAccessible();

                        $like->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.articleComment',
                            [
                                'commentAuthor' => $comment->userID !== null ? $comment->getUserProfile() : null,
                                'comment' => $comment,
                                'articleContent' => $articleContents[$comment->objectID],
                                'reaction' => $like,
                                'author' => $like->getUserProfile(),
                            ]
                        ));
                        $like->setLink($comment->getLink());
                        $like->setDescription(\strip_tags($comment->getExcerpt()));
                    }
                }
            } else {
                // response like
                if (isset($responses[$like->objectID])) {
                    $response = $responses[$like->objectID];
                    $comment = $comments[$response->commentID];

                    if (
                        isset($articleContents[$comment->objectID])
                        && $articleContents[$comment->objectID]->getArticle()->canRead()
                    ) {
                        $like->setIsAccessible();

                        $like->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.articleComment.response',
                            [
                                'responseAuthor' => $response->userID !== null ? $response->getUserProfile() : null,
                                'commentAuthor' => $comment->userID !== null ? $comment->getUserProfile() : null,
                                'articleContent' => $articleContents[$comment->objectID],
                                'reaction' => $like,
                                'author' => $like->getUserProfile(),
                            ]
                        ));
                        $like->setLink($response->getLink());
                        $like->setDescription(\strip_tags($response->getExcerpt()));
                    }
                }
            }
        }
    }

    #[\Override]
    public function isContentAuthor(Comment|CommentResponse $commentOrResponse)
    {
        $articleContent = ArticleContentRuntimeCache::getInstance()
            ->getObject($this->getObjectID($commentOrResponse));

        return $commentOrResponse->userID !== null && $articleContent->getArticle()->userID === $commentOrResponse->userID;
    }
}
