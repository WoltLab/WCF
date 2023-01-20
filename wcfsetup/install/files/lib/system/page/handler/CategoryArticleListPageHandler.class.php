<?php

namespace wcf\system\page\handler;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\ViewableArticle;

/**
 * Menu page handler for the category article list page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class CategoryArticleListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TDecoratedCategoryOnlineLocationLookupPageHandler;

    /**
     * @see TDecoratedCategoryLookupPageHandler::getDecoratedCategoryClass()
     */
    protected function getDecoratedCategoryClass()
    {
        return ArticleCategory::class;
    }

    /**
     * @inheritDoc
     * @since       5.2
     */
    public function getOutstandingItemCount($objectID = null)
    {
        return ViewableArticle::getUnreadArticlesForCategory($objectID);
    }
}
