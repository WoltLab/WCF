<?php

namespace wcf\system\acp\dashboard;

use wcf\system\acp\dashboard\box\IAcpDashboardBox;
use wcf\system\acp\dashboard\event\AcpDashboardCollecting;
use wcf\system\event\EventHandler;


/**
 * Represents the acp dashboard.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class AcpDashboard
{
    /**
     * @var IAcpDashboardBox[]
     */
    private array $boxes;

    public function __construct()
    {
        $event = new AcpDashboardCollecting();
        EventHandler::getInstance()->fire($event);

        $this->boxes = $event->getBoxes();
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getAccessibleBoxes(): array
    {
        return \array_filter($this->getBoxes(), static fn (IAcpDashboardBox $box) => $box->isAccessible());
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getVisibleBoxes(): array
    {
        return \array_filter($this->getAccessibleBoxes(), static fn (IAcpDashboardBox $box) => $box->hasContent());
    }
}
