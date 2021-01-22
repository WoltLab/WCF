<?php

namespace wcf\data\article;

use wcf\data\article\category\ArticleCategory;
use wcf\system\WCF;

/**
 * Represents a list of accessible articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Article
 * @since   3.0
 */
class AccessibleArticleList extends ViewableArticleList
{
    /**
     * Creates a new AccessibleArticleList object.
     */
    public function __construct()
    {
        parent::__construct();

        // get accessible categories
        $accessibleCategoryIDs = ArticleCategory::getAccessibleCategoryIDs();
        if (empty($accessibleCategoryIDs)) {
            $this->getConditionBuilder()->add('1=0');
        } else {
            $this->getConditionBuilder()->add('article.categoryID IN (?)', [$accessibleCategoryIDs]);

            if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
                if (WCF::getSession()->getPermission('admin.content.article.canManageOwnArticles')) {
                    $this->getConditionBuilder()->add(
                        '(article.userID = ? OR (article.isDeleted = ? AND article.publicationStatus = ?))',
                        [WCF::getUser()->userID, 0, Article::PUBLISHED]
                    );
                } else {
                    $this->getConditionBuilder()->add('article.isDeleted = ?', [0]);
                    $this->getConditionBuilder()->add('article.publicationStatus = ?', [Article::PUBLISHED]);
                }
            }
        }
    }
}
