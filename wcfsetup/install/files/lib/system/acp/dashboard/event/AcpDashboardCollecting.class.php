<?php

namespace wcf\system\acp\dashboard\event;

use wcf\system\acp\dashboard\box\IAcpDashboardBox;
use wcf\system\event\IEvent;

/**
 * Requests the collection of boxes for the acp dashboard.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class AcpDashboardCollecting implements IEvent
{
    /**
     * @var IAcpDashboardBox[]
     */
    private array $boxes = [];

    /**
     * Registers a new box.
     */
    public function register(IAcpDashboardBox $box): void
    {
        $this->boxes[$box->getName()] = $box;
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }
}
