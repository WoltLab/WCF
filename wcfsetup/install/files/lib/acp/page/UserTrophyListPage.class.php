<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\UserTrophyGridView;

/**
 * User trophy list page.
 *
 * @author  Olaf Braun, Joshua Ruesweg
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<UserTrophyGridView>
 */
final class UserTrophyListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canAwardTrophy'];

    #[\Override]
    protected function createGridView(): UserTrophyGridView
    {
        return new UserTrophyGridView();
    }
}
