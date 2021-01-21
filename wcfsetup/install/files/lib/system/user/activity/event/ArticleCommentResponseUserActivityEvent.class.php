<?php

namespace wcf\system\user\activity\event;

use wcf\data\article\ViewableArticleList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for responses to article comments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Activity\Event
 * @since   3.0
 */
class ArticleCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    use TCommentResponseUserActivityEvent;

    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $this->readResponseData($events);

        // fetch articles
        $articles = $articleContentToArticle = [];
        if (!empty($this->commentObjectIDs)) {
            $articleList = new ViewableArticleList();
            $articleList->getConditionBuilder()->add("article.articleID IN (SELECT articleID FROM wcf" . WCF_N . "_article_content WHERE articleContentID IN (?))", [$this->commentObjectIDs]);
            $articleList->readObjects();
            foreach ($articleList as $article) {
                $articles[$article->articleID] = $article;

                $articleContentToArticle[$article->getArticleContent()->articleContentID] = $article->articleID;
            }
        }

        // set message
        foreach ($events as $event) {
            if (isset($this->responses[$event->objectID])) {
                $response = $this->responses[$event->objectID];
                $comment = $this->comments[$response->commentID];
                if (isset($articleContentToArticle[$comment->objectID]) && isset($this->commentAuthors[$comment->userID])) {
                    $article = $articles[$articleContentToArticle[$comment->objectID]];

                    // check permissions
                    if (!$article->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // title
                    $text = WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.articleCommentResponse', [
                        'commentAuthor' => $this->commentAuthors[$comment->userID],
                        'commentID' => $comment->commentID,
                        'responseID' => $response->responseID,
                        'article' => $article,
                    ]);
                    $event->setTitle($text);

                    // description
                    $event->setDescription($response->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
