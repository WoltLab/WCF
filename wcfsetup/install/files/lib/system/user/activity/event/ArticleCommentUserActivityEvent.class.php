<?php

namespace wcf\system\user\activity\event;

use wcf\data\article\ViewableArticleList;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for article comments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        if (!\MODULE_ARTICLE) {
            return;
        }

        $commentIDs = [];
        foreach ($events as $event) {
            $commentIDs[] = $event->objectID;
        }

        // fetch comments
        $comments = ViewableCommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // fetch articles
        $articleContentIDs = [];
        foreach ($comments as $comment) {
            $articleContentIDs[] = $comment->objectID;
        }

        $articles = $articleContentToArticle = [];
        if (!empty($articleContentIDs)) {
            $articleList = new ViewableArticleList();
            $articleList->getConditionBuilder()->add(
                "article.articleID IN (
                    SELECT  articleID
                    FROM    wcf" . WCF_N . "_article_content
                    WHERE   articleContentID IN (?)
                )",
                [$articleContentIDs]
            );
            $articleList->readObjects();
            foreach ($articleList as $article) {
                $articles[$article->articleID] = $article;

                $articleContentToArticle[$article->getArticleContent()->articleContentID] = $article->articleID;
            }
        }

        // set message
        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                // short output
                $comment = $comments[$event->objectID];
                if (isset($articleContentToArticle[$comment->objectID])) {
                    $article = $articles[$articleContentToArticle[$comment->objectID]];

                    // check permissions
                    if (!$article->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    $event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.articleComment', [
                        'article' => $article,
                        'commentID' => $comment->commentID,
                        'author' => $event->getUserProfile(),
                    ]));
                    $event->setDescription(
                        StringUtil::encodeHTML(
                            StringUtil::truncate($comment->getPlainTextMessage(), 500)
                        ),
                        true
                    );
                    $event->setLink($comment->getLink());

                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
