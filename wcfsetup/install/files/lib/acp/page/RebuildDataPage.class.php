<?php

namespace wcf\acp\page;

use wcf\event\worker\RebuildWorkerCollecting;
use wcf\page\AbstractPage;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Show the list of available rebuild data options.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class RebuildDataPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.rebuildData';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * @var iterable
     */
    private iterable $workers;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get object types
        $event = new RebuildWorkerCollecting();
        EventHandler::getInstance()->fire($event);

        $this->workers = $event->getWorkers();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'workers' => \iterator_to_array($this->workers),
        ]);
    }
}
