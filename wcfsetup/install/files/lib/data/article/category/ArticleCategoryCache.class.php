<?php

namespace wcf\data\article\category;

use wcf\data\article\Article;
use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the article category cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleCategoryCache extends SingletonFactory
{
    /**
     * number of total articles
     * @var int[]
     */
    protected $articles;

    /**
     * Calculates the number of articles.
     */
    protected function initArticles()
    {
        $this->articles = [];

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('publicationStatus = ?', [Article::PUBLISHED]);
        if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
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
     * @param int $categoryID
     * @return      int
     */
    protected function countArticles(array $categoryToParent, array &$articles, $categoryID)
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
     * @param int $categoryID
     * @return  int
     */
    public function getArticles($categoryID)
    {
        if ($this->articles === null) {
            $this->initArticles();
        }

        if (isset($this->articles[$categoryID])) {
            return $this->articles[$categoryID];
        }

        return 0;
    }
}
