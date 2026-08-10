<?php

namespace wcf\system\page\handler;

use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Page handler implementation for the page showing the list of articles in watched categories.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WatchedArticleListPageHandler extends AbstractMenuPageHandler
{
    #[\Override]
    public function getOutstandingItemCount(?int $objectID = null)
    {
        return self::getWatchedUnreadArticles();
    }

    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        return ArticleCategory::getSubscribedCategoryIDs() !== [];
    }

    private static function getWatchedUnreadArticles(): int
    {
        if (WCF::getUser()->isGuest()) {
            return 0;
        }

        static $unreadWatchedArticles = null;

        if ($unreadWatchedArticles === null) {
            $unreadWatchedArticles = UserStorageHandler::getInstance()->getField('unreadWatchedArticles');

            // cache does not exist or is outdated
            if ($unreadWatchedArticles === null) {
                $unreadWatchedArticles = 0;
                $categoryIDs = ArticleCategory::getSubscribedCategoryIDs();
                if ($categoryIDs !== []) {
                    $conditionBuilder = new PreparedStatementConditionBuilder();
                    $conditionBuilder->add('article.categoryID IN (?)', [$categoryIDs]);
                    $conditionBuilder->add(
                        'article.time > ?',
                        [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
                    );
                    $conditionBuilder->add('article.isDeleted = ?', [0]);
                    $conditionBuilder->add('article.publicationStatus = ?', [Article::PUBLISHED]);
                    $conditionBuilder->add('(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');

                    $sql = "SELECT      COUNT(*)
                            FROM        wcf1_article article
                            LEFT JOIN   wcf1_tracked_visit tracked_visit
                            ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                                    AND tracked_visit.objectID = article.articleID
                                    AND tracked_visit.userID = " . WCF::getUser()->userID . "
                            " . $conditionBuilder;
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute($conditionBuilder->getParameters());
                    $unreadWatchedArticles = $statement->fetchSingleColumn();
                }

                // update storage unreadEntries
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    'unreadWatchedArticles',
                    $unreadWatchedArticles
                );
            }
        }

        return $unreadWatchedArticles;
    }
}
