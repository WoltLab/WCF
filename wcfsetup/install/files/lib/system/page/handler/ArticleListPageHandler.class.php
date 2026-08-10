<?php

namespace wcf\system\page\handler;

use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;

/**
 * Page handler implementation for the page showing the list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleListPageHandler extends AbstractMenuPageHandler
{
    #[\Override]
    public function getOutstandingItemCount(?int $objectID = null)
    {
        return Article::getUnreadArticles();
    }

    /**
     * @since   5.2
     */
    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        return ArticleCategory::getAccessibleCategoryIDs() !== [];
    }
}
