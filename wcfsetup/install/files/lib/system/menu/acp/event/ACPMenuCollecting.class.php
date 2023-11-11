<?php

namespace wcf\system\menu\acp\event;

use wcf\system\event\IEvent;
use wcf\system\menu\acp\ACPMenuItem;

/**
 * Requests the collection of acp menu items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ACPMenuCollecting implements IEvent
{
    /**
     * @var ACPMenuItem[]
     */
    private array $items = [];

    public function register(ACPMenuItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return ACPMenuItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
