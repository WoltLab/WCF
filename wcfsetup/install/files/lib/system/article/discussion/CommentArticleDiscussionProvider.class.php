<?php

namespace wcf\system\article\discussion;

use wcf\data\article\Article;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\view\CommentsView;
use wcf\system\WCF;

/**
 * The built-in discussion provider using the native comment system.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentArticleDiscussionProvider extends AbstractArticleDiscussionProvider
{
    #[\Override]
    public function getDiscussionCount()
    {
        return $this->articleContent !== null
            ? $this->articleContent->comments
            : $this->article->getArticleContent()->comments;
    }

    #[\Override]
    public function getDiscussionCountPhrase()
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.article.articleComments', [
            'articleContent' => $this->articleContent ?: $this->article->getArticleContent(),
            'article' => $this->article, // kept line for backward compatibility in 3rd party translations
        ]);
    }

    #[\Override]
    public function getDiscussionLink()
    {
        return $this->articleContent->getLink() . '#comments';
    }

    #[\Override]
    public function renderDiscussions()
    {
        $commentsView = new CommentsView(
            'com.woltlab.wcf.articleComment',
            $this->articleContent->articleContentID,
            'articleCommentList',
            WCF::getSession()->hasPermission('user.article.canAddComment'),
            $this->articleContent->comments
        );

        return $commentsView->render();
    }

    #[\Override]
    public function migrateDiscussions(ArticleContent $oldContent, ArticleContent $newContent): void
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'com.woltlab.wcf.comment.commentableContent',
            'com.woltlab.wcf.articleComment',
        );
        \assert($objectTypeID !== null);

        $sql = "UPDATE  wcf1_comment
                SET     objectID = ?
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $newContent->articleContentID,
            $objectTypeID,
            $oldContent->articleContentID,
        ]);

        ArticleContentBuilder::forUpdate($newContent)
            ->incrementComments($oldContent->comments)
            ->update();
    }

    #[\Override]
    public static function isResponsible(Article $article)
    {
        return $article->enableComments !== 0;
    }
}
