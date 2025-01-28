<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\admin\ExceptionLogGridView;
use wcf\system\registry\RegistryHandler;

/**
 * Shows the exception log.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ExceptionLogViewPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.log.exception';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canViewLog'];

    #[\Override]
    public function readData()
    {
        $this->markNotificationsAsRead();

        parent::readData();
    }

    private function markNotificationsAsRead(): void
    {
        RegistryHandler::getInstance()->set('com.woltlab.wcf', 'exceptionMailerTimestamp', TIME_NOW);
    }

    #[\Override]
    protected function createGridViewController(): AbstractGridView
    {
        return new ExceptionLogGridView(true);
    }
}
