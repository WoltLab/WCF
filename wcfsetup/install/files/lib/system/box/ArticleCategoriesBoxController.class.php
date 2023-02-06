<?php

namespace wcf\system\box;

use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\category\CategoryNodeTree;
use wcf\page\ArticleListPage;
use wcf\page\ArticlePage;
use wcf\page\CategoryArticleListPage;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;

/**
 * Box for article categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleCategoriesBoxController extends AbstractCategoriesBoxController
{
    protected function getNodeTree(): CategoryNodeTree
    {
        return new ArticleCategoryNodeTree('com.woltlab.wcf.article.category');
    }

    protected function getActiveCategory(): ?AbstractDecoratedCategory
    {
        $activeCategory = null;
        if (RequestHandler::getInstance()->getActiveRequest() !== null) {
            if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof CategoryArticleListPage || RequestHandler::getInstance()->getActiveRequest()->getRequestObject() instanceof ArticlePage) {
                if (RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category !== null) {
                    $activeCategory = RequestHandler::getInstance()->getActiveRequest()->getRequestObject()->category;
                }
            }
        }

        return $activeCategory;
    }

    protected function getResetLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(ArticleListPage::class);
    }
}
