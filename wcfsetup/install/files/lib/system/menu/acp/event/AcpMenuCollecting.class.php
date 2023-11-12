<?php

namespace wcf\system\menu\acp\event;

use wcf\system\event\IEvent;
use wcf\system\menu\acp\AcpMenuItem;

/**
 * Requests the collection of acp menu items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class AcpMenuCollecting implements IEvent
{
    /**
     * @var AcpMenuItem[]
     */
    private array $items = [];

    public function register(AcpMenuItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return AcpMenuItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
