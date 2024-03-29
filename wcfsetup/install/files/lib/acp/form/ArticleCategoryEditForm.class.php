<?php

namespace wcf\acp\form;

/**
 * Shows the article category edit form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class ArticleCategoryEditForm extends ArticleCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.category.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';
}
