<?php

namespace wcf\data\article\category;

use wcf\data\article\Article;
use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Manages the article category cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleCategoryCache extends SingletonFactory
{
    /**
     * number of total articles
     * @var array<int, int>
     */
    protected $articles;

    /**
     * number of unread articles
     * @var array<int, int>
     */
    protected array $unreadArticles;

    /**
     * Calculates the number of articles.
     *
     * @return void
     */
    protected function initArticles()
    {
        $this->articles = [];

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('publicationStatus = ?', [Article::PUBLISHED]);
        if (!WCF::getSession()->hasPermission('admin.content.article.canManageArticle')) {
            $conditionBuilder->add('isDeleted = ?', [0]);
        }

        $sql = "SELECT      COUNT(*) AS count, categoryID
                FROM        wcf1_article
                " . $conditionBuilder . "
                GROUP BY    categoryID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $articles = $statement->fetchMap('categoryID', 'count');

        $categoryToParent = [];
        /** @var Category $category */
        foreach (CategoryHandler::getInstance()->getCategories(ArticleCategory::OBJECT_TYPE_NAME) as $category) {
            if (!isset($categoryToParent[$category->parentCategoryID])) {
                $categoryToParent[$category->parentCategoryID] = [];
            }
            $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
        }

        $this->countArticles($categoryToParent, $articles, 0);
    }

    /**
     * Counts the articles contained in this category and its children.
     *
     * @param int[][] $categoryToParent
     * @param int[] $articles
     * @return      int
     */
    protected function countArticles(array $categoryToParent, array &$articles, int $categoryID)
    {
        $count = (isset($articles[$categoryID])) ? $articles[$categoryID] : 0;
        if (isset($categoryToParent[$categoryID])) {
            foreach ($categoryToParent[$categoryID] as $childCategoryID) {
                $count += $this->countArticles($categoryToParent, $articles, $childCategoryID);
            }
        }

        if ($categoryID) {
            $this->articles[$categoryID] = $count;
        }

        return $count;
    }

    /**
     * Returns the number of articles in the category with the given id.
     *
     * @return  int
     */
    public function getArticles(int $categoryID)
    {
        if ($this->articles === null) {
            $this->initArticles();
        }

        if (isset($this->articles[$categoryID])) {
            return $this->articles[$categoryID];
        }

        return 0;
    }

    /**
     * Calculates the number of unread articles.
     */
    protected function initUnreadArticles(): void
    {
        $this->unreadArticles = [];

        if (WCF::getUser()->isGuest()) {
            return;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            'article.time > ?',
            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.article')]
        );
        $conditionBuilder->add('article.isDeleted = ?', [0]);
        $conditionBuilder->add('article.publicationStatus = ?', [Article::PUBLISHED]);
        $conditionBuilder->add('(article.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');

        $sql = "SELECT      COUNT(*) AS count, article.categoryID
                FROM        wcf1_article article
                LEFT JOIN   wcf1_tracked_visit tracked_visit
                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.article') . "
                        AND tracked_visit.objectID = article.articleID
                        AND tracked_visit.userID = " . WCF::getUser()->userID . "
                " . $conditionBuilder . "
                GROUP BY    article.categoryID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $this->unreadArticles = $statement->fetchMap('categoryID', 'count');
    }

    /**
     * Returns the number of unread articles in the category with the given id.
     */
    public function getUnreadArticles(int $categoryID): int
    {
        if (!isset($this->unreadArticles)) {
            $this->initUnreadArticles();
        }

        return $this->unreadArticles[$categoryID] ?? 0;
    }
}
