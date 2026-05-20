<?php

namespace wcf\command\menu\item;

use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemEditor;
use wcf\event\menu\item\MenuItemDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a menu item.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DisableMenuItem
{
    public function __construct(private readonly MenuItem $menuItem) {}

    public function __invoke(): void
    {
        (new MenuItemEditor($this->menuItem))->update([
            'isDisabled' => 1,
        ]);

        MenuItemEditor::resetCache();

        $event = new MenuItemDisabled($this->menuItem);
        EventHandler::getInstance()->fire($event);
    }
}
