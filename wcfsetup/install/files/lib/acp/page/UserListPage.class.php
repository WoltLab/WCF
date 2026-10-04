<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\UserGridView;

/**
 * Shows the list of users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<UserGridView>
 */
final class UserListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canSearchUser'];

    #[\Override]
    protected function createGridView(): UserGridView
    {
        return new UserGridView();
    }
}
