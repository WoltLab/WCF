<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\admin\ModificationLogGridView;

/**
 * Shows a list of modification log items.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    ModificationLogGridView    $gridView
 * @since       5.2
 */
class ModificationLogListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.modification';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    #[\Override]
    protected function createGridViewController(): AbstractGridView
    {
        return new ModificationLogGridView();
    }
}
