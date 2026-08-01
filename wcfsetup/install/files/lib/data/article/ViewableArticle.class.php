<?php

namespace wcf\data\article;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a viewable article.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `Article` instead.
 *
 * @method          ArticleContent|ViewableArticleContent|null   getArticleContent()
 * @mixin           Article
 * @property-read   int|null $visitTime  last time the active user has visited the time or `null` if object has not been fetched via `ViewableArticleList` or if the active user is a guest
 * @extends DatabaseObjectDecorator<Article>
 */
class ViewableArticle extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Article::class;

    /**
     * number of unread articles in watched categories
     * @var int
     * @since   5.2
     */
    protected static $unreadWatchedArticles;

    /**
     * number of unread articles ordered by categories
     * @var int[]
     * @since   5.2
     */
    protected static $unreadArticlesByCategory;

    /**
     * Returns a specific article decorated as viewable article or `null` if it does not exist.
     *
     * @param bool $enableContentLoading Enables/disables the loading of article content objects
     * @return  ViewableArticle
     */
    public static function getArticle(int $articleID, bool $enableContentLoading = true)
    {
        $list = new ViewableArticleList();
        $list->enableContentLoading($enableContentLoading);
        $list->setObjectIDs([$articleID]);
        $list->readObjects();

        return $list->getSingleObject();
    }

    /**
     * Returns the number of unread articles.
     *
     * @return  int
     */
    public static function getUnreadArticles()
    {
        return Article::getUnreadArticles();
    }

    /**
     * Returns the number of unread articles for a specific category.
     *
     * @return  int
     * @since       5.2
     * @deprecated 6.3
     */
    public static function getUnreadArticlesForCategory(int $articleCategoryID)
    {
        if (self::$unreadArticlesByCategory === null) {
            self::$unreadArticlesByCategory = [];

            if (!WCF::getUser()->isGuest()) {
                $unreadArticlesByCategory = UserStorageHandler::getInstance()->getField('unreadArticlesByCategory');

                // cache does not exist or is outdated
                if ($unreadArticlesByCategory === null) {
                    self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                    // update storage unreadEntries
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'unreadArticlesByCategory',
                        \serialize(self::$unreadArticlesByCategory)
                    );
                } else {
                    $unreadArticlesByCategory = \unserialize($unreadArticlesByCategory);

                    if (isset($unreadArticlesByCategory[$articleCategoryID])) {
                        self::$unreadArticlesByCategory = $unreadArticlesByCategory;
                    } else {
                        self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                        // update storage unreadEntries
                        UserStorageHandler::getInstance()->update(
                            WCF::getUser()->userID,
                            'unreadArticlesByCategory',
                            \serialize(self::$unreadArticlesByCategory)
                        );
                    }
                }
            } else {
                self::$unreadArticlesByCategory[$articleCategoryID] = 0;
            }
        } elseif (!isset(self::$unreadArticlesByCategory[$articleCategoryID])) {
            if (!WCF::getUser()->isGuest()) {
                self::$unreadArticlesByCategory[$articleCategoryID] = self::fetchUnreadArticlesForCategory($articleCategoryID);

                // update storage unreadEntries
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    'unreadArticlesByCategory',
                    \serialize(self::$unreadArticlesByCategory)
                );
            } else {
                self::$unreadArticlesByCategory[$articleCategoryID] = 0;
            }
        }

        return self::$unreadArticlesByCategory[$articleCategoryID];
    }

    /**
     * Returns the unread article count for a specific category.
     *
     * @return      int
     * @since       5.2
     */
    private static function fetchUnreadArticlesForCategory(int $articleCategoryID)
    {
        $accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();

        if (!\in_array($articleCategoryID, $accessibleCategoryIDs)) {
            // the category is not accessible
            return 0;
        }

        $category = ArticleCategory::getCategory($articleCategoryID);

        if ($category === null) {
            throw new \InvalidArgumentException('The given article category id "' . $articleCategoryID . '" is not valid.');
        }

        $categoryIDs = \array_intersect(\array_merge(\array_map(static function ($category) {
            /** @var ArticleCategory $category */
            return $category->categoryID;
        }, $category->getChildCategories()), [$articleCategoryID]), $accessibleCategoryIDs);

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

        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of unread articles in watched categories.
     *
     * @return  int
     * @since       5.2
     * @deprecated 6.3
     */
    public static function getWatchedUnreadArticles()
    {
        if (self::$unreadWatchedArticles === null) {
            self::$unreadWatchedArticles = 0;

            if (!WCF::getUser()->isGuest()) {
                $unreadArticles = UserStorageHandler::getInstance()->getField('unreadWatchedArticles');

                // cache does not exist or is outdated
                if ($unreadArticles === null) {
                    $categoryIDs = ArticleCategory::getSubscribedCategoryIDs();
                    if (!empty($categoryIDs)) {
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
                        self::$unreadWatchedArticles = $statement->fetchSingleColumn();
                    }

                    // update storage unreadEntries
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'unreadWatchedArticles',
                        self::$unreadWatchedArticles
                    );
                } else {
                    self::$unreadWatchedArticles = $unreadArticles;
                }
            }
        }

        return self::$unreadWatchedArticles;
    }
}
