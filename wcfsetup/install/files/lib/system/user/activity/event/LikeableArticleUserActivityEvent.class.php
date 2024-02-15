<?php

namespace wcf\system\user\activity\event;

use wcf\data\article\ViewableArticleList;
use wcf\system\reaction\ReactionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for liked cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class LikeableArticleUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        if (!\MODULE_ARTICLE) {
            return;
        }

        $articleIDs = [];
        foreach ($events as $event) {
            $articleIDs[] = $event->objectID;
        }

        // fetch articles
        $articleList = new ViewableArticleList();
        $articleList->setObjectIDs($articleIDs);
        $articleList->readObjects();
        $articles = $articleList->getObjects();

        // set message
        foreach ($events as $event) {
            if (isset($articles[$event->objectID])) {
                $article = $articles[$event->objectID];

                // check permissions
                if (!$article->canRead()) {
                    continue;
                }
                $event->setIsAccessible();

                $event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity.likedArticle', [
                    'article' => $article,
                    'reactionType' => ReactionHandler::getInstance()->getReactionTypeByID(
                        $event->reactionTypeID ?? $event->reactionType->reactionTypeID
                    ),
                    'author' => $event->getUserProfile(),
                ]));
                $event->setLink($article->getLink());
            } else {
                $event->setIsOrphaned();
            }
        }
    }
}
