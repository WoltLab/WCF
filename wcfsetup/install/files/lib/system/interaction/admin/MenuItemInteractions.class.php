<?php

namespace wcf\system\interaction\admin;

use wcf\data\menu\item\MenuItem;
use wcf\event\interaction\admin\MenuItemInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;

/**
 * Interaction provider for menu items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MenuItemInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction(
                'core/menus/items/%s',
                static fn(MenuItem $object) => $object->canDelete()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new MenuItemInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return MenuItem::class;
    }
}
