<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\admin\UserRankGridView;

/**
 * Lists available user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    UserRankGridView    $gridView
 */
class UserRankListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.rank.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_USER_RANK'];

    #[\Override]
    protected function createGridViewController(): AbstractGridView
    {
        return new UserRankGridView();
    }
}
