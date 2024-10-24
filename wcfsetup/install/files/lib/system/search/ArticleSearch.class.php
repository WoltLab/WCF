<?php

namespace wcf\system\search;

use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\data\article\content\SearchResultArticleContent;
use wcf\data\article\content\SearchResultArticleContentList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * An implementation of ISearchProvider for searching in articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleSearch extends AbstractSearchProvider
{
    /**
     * @var int
     */
    private $articleCategoryID = 0;

    /**
     * @var SearchResultArticleContent[]
     */
    private $messageCache = [];

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void
    {
        $list = new SearchResultArticleContentList();
        $list->setObjectIDs($objectIDs);
        $list->readObjects();
        foreach ($list->getObjects() as $content) {
            $this->messageCache[$content->articleContentID] = $content;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $objectID): ?ISearchResultObject
    {
        return $this->messageCache[$objectID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'wcf1_article_content';
    }

    /**
     * @inheritDoc
     */
    public function getIDFieldName(): string
    {
        return $this->getTableName() . '.articleContentID';
    }

    /**
     * @inheritDoc
     */
    public function getSubjectFieldName(): string
    {
        return $this->getTableName() . '.title';
    }

    /**
     * @inheritDoc
     */
    public function getUsernameFieldName(): string
    {
        return 'wcf1_article.username';
    }

    /**
     * @inheritDoc
     */
    public function getTimeFieldName(): string
    {
        return 'wcf1_article.time';
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        if (!empty($parameters['articleCategoryID'])) {
            $this->articleCategoryID = \intval($parameters['articleCategoryID']);
        }

        $articleCategoryIDs = $this->getArticleCategoryIDs($this->articleCategoryID);
        $accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();
        if (!empty($articleCategoryIDs)) {
            $articleCategoryIDs = \array_intersect($articleCategoryIDs, $accessibleCategoryIDs);
        } else {
            $articleCategoryIDs = $accessibleCategoryIDs;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        if (empty($articleCategoryIDs)) {
            $conditionBuilder->add('1=0');
        } else {
            $conditionBuilder->add(
                'wcf1_article.categoryID IN (?) AND wcf1_article.publicationStatus = ?',
                [$articleCategoryIDs, Article::PUBLISHED]
            );
        }

        return $conditionBuilder;
    }

    private function getArticleCategoryIDs(int $categoryID): array
    {
        $categoryIDs = [];

        if ($categoryID) {
            if (($category = ArticleCategory::getCategory($categoryID)) !== null) {
                $categoryIDs[] = $categoryID;
                foreach ($category->getAllChildCategories() as $childCategory) {
                    $categoryIDs[] = $childCategory->categoryID;
                }
            }
        }

        return $categoryIDs;
    }

    /**
     * @inheritDoc
     */
    public function getJoins(): string
    {
        return '
            INNER JOIN  wcf1_article
            ON          wcf1_article.articleID = ' . $this->getTableName() . '.articleID';
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplateName(): string
    {
        return 'searchArticle';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): ?array
    {
        return ['articleCategoryID' => $this->articleCategoryID];
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        return MODULE_ARTICLE && SEARCH_ENABLE_ARTICLES;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
        WCF::getTPL()->assign([
            'articleCategoryList' => (new ArticleCategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator(),
        ]);
    }
}
