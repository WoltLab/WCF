<?php

namespace wcf\acp\page;

/**
 * Shows the list article categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleCategoryListPage extends AbstractCategoryListPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.category.list';

    /**
     * @inheritDoc
     */
    public $objectTypeName = 'com.woltlab.wcf.article.category';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];
}
