<?php

namespace wcf\system\listView\user;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\system\WCF;

/**
 * List view for the list of articles filtered by a category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class CategoryArticleListView extends ArticleListView
{
    public function __construct(public readonly int $categoryID)
    {
        parent::__construct();

        $category = ArticleCategory::getCategory($this->categoryID);
        if ($category !== null && $category->sortField !== null && $category->sortField !== '') {
            // Legacy categories may still store the previously offered `publicationDate` value,
            // which never matched a registered sort field. Map it to `time`, which is the
            // semantically equivalent column actually exposed by the list view.
            $sortField = $category->sortField === 'publicationDate' ? 'time' : $category->sortField;
            $this->setDefaultSortField($sortField);
            if ($category->sortOrder === 'ASC' || $category->sortOrder === 'DESC') {
                $this->setDefaultSortOrder($category->sortOrder);
            }
        }
    }

    #[\Override]
    protected function createObjectList(): CategoryArticleList
    {
        $list = new CategoryArticleList($this->categoryID, true);
        if ($list->sqlSelects !== '') {
            $list->sqlSelects .= ',';
        }
        $list->sqlSelects .= "(
            SELECT  title
            FROM    wcf1_article_content
            WHERE   articleID = article.articleID
                AND (
                        languageID IS NULL
                     OR languageID = " . WCF::getLanguage()->languageID . "
                    )
            LIMIT   1
        ) AS title";

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return parent::isAccessible() && ArticleCategory::getCategory($this->categoryID)->isAccessible();
    }

    #[\Override]
    protected function getAccessibleLabelGroups(): array
    {
        return ArticleCategory::getCategory($this->categoryID)->getLabelGroups('canViewLabel');
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['categoryID' => $this->categoryID];
    }
}
