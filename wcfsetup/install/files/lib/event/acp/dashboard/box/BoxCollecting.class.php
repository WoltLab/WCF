<?php

namespace wcf\event\acp\dashboard\box;

use wcf\event\IPsr14Event;
use wcf\system\acp\dashboard\box\IAcpDashboardBox;

/**
 * Requests the collection of boxes for the acp dashboard.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class BoxCollecting implements IPsr14Event
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
