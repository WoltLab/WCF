<?php

namespace wcf\event\menu\item;

use wcf\data\menu\item\MenuItem;
use wcf\event\IPsr14Event;

/**
 * Indicates that a menu item has been disabled.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MenuItemDisabled implements IPsr14Event
{
    public function __construct(public readonly MenuItem $menuItem) {}
}
