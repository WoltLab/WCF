<?php

namespace wcf\system\interaction\admin;

use wcf\acp\form\MenuItemAddForm;
use wcf\acp\page\MenuItemListPage;
use wcf\data\DatabaseObject;
use wcf\data\menu\Menu;
use wcf\event\interaction\admin\MenuInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\LinkInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Interaction provider for menus.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class MenuInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new DeleteInteraction(
                "core/menus/%s",
                static fn(Menu $menu) => $menu->canDelete()
            ),
            new LinkInteraction("show-items", MenuItemListPage::class, "wcf.acp.menu.showItems"),
            new class("add-items") extends AbstractInteraction {
                #[\Override]
                public function render(DatabaseObject $object): string
                {
                    \assert($object instanceof Menu);
                    $href = LinkHandler::getInstance()->getControllerLink(
                        MenuItemAddForm::class,
                        ['menuID' => $object->menuID]
                    );
                    $title = WCF::getLanguage()->get("wcf.acp.menu.item.add");

                    return \sprintf('<a href="%s">%s</a>', StringUtil::encodeHTML($href), $title);
                }
            }
        ]);

        EventHandler::getInstance()->fire(
            new MenuInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Menu::class;
    }
}
