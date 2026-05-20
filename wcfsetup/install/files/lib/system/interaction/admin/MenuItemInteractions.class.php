<?php

namespace wcf\system\interaction\admin;

use wcf\acp\form\MenuItemAddForm;
use wcf\data\DatabaseObject;
use wcf\data\menu\item\MenuItem;
use wcf\event\interaction\admin\MenuItemInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\LinkInteraction;
use wcf\system\request\LinkHandler;

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
            new class('add-child-node', MenuItemAddForm::class, 'wcf.acp.menu.item.addChildNode') extends LinkInteraction {
                #[\Override]
                protected function getLink(DatabaseObject $object): string
                {
                    \assert($object instanceof MenuItem);

                    return LinkHandler::getInstance()->getControllerLink(
                        $this->controllerClass,
                        [
                            'menuID' => $object->menuID,
                            'parentItemID' => $object->getObjectID(),
                        ]
                    );
                }
            },
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
