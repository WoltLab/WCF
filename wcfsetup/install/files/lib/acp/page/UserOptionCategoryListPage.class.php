<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\UserOptionCategoryGridView;

/**
 * Shows a list of user option categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<UserOptionCategoryGridView>
 */
final class UserOptionCategoryListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    #[\Override]
    protected function createGridView(): UserOptionCategoryGridView
    {
        return new UserOptionCategoryGridView();
    }
}
