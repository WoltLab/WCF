<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\view\grid\AbstractGridView;
use wcf\system\view\grid\CronjobLogGridView;

/**
 * Shows cronjob log information.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    CronjobLogGridView    $gridView
 */
class CronjobLogListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.cronjob';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canManageCronjob'];

    #[\Override]
    protected function createGridViewController(): AbstractGridView
    {
        return new CronjobLogGridView();
    }
}
