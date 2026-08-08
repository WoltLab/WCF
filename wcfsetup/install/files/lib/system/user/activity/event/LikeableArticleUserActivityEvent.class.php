<?php

namespace wcf\system\user\activity\event;

use wcf\data\article\ArticleList;
use wcf\system\reaction\ReactionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for liked cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LikeableArticleUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    #[\Override]
    public function prepare(array $events)
    {
        if (\MODULE_ARTICLE === 0) {
            return;
        }

        $articleIDs = [];
        foreach ($events as $event) {
            $articleIDs[] = $event->objectID;
        }

        // fetch articles
        $articleList = new ArticleList();
        $articleList->setObjectIDs($articleIDs);
        $articleList->readObjects();
        $articles = $articleList->getObjects();

        // set message
        foreach ($events as $event) {
            if (isset($articles[$event->objectID])) {
                $article = $articles[$event->objectID];

                $reactionType = ReactionHandler::getInstance()->getReactionTypeByID(
                    $event->reactionTypeID ?? $event->reactionType->reactionTypeID
                );
                if ($reactionType === null) {
                    $event->setIsOrphaned();
                    continue;
                }

                // check permissions
                if (!$article->canRead()) {
                    continue;
                }
                $event->setIsAccessible();

                $event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.likedArticle', [
                    'article' => $article,
                    'reactionType' => $reactionType,
                    'author' => $event->getUserProfile(),
                ]));
                $event->setLink($article->getLink());
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
