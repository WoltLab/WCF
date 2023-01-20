<?php

namespace wcf\system\page\handler;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\ViewableArticle;

/**
 * Page handler implementation for the page showing the list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class ArticleListPageHandler extends AbstractMenuPageHandler
{
    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        return ViewableArticle::getUnreadArticles();
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function isVisible($objectID = null)
    {
        return !empty(ArticleCategory::getAccessibleCategoryIDs());
    }
}
