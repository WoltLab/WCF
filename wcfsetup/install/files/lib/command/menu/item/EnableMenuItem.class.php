<?php

namespace wcf\command\menu\item;

use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemEditor;
use wcf\event\menu\item\MenuItemEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a menu item.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class EnableMenuItem
{
    public function __construct(private readonly MenuItem $menuItem) {}

    public function __invoke(): void
    {
        (new MenuItemEditor($this->menuItem))->update([
            'isDisabled' => 0,
        ]);

        MenuItemEditor::resetCache();

        $event = new MenuItemEnabled($this->menuItem);
        EventHandler::getInstance()->fire($event);
    }
}
