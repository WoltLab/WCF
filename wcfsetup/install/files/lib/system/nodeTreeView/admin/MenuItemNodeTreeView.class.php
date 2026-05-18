<?php

namespace wcf\system\nodeTreeView\admin;

use wcf\acp\form\MenuItemEditForm;
use wcf\data\IObjectTreeNode;
use wcf\data\menu\item\MenuItemNode;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\system\interaction\admin\MenuItemInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\nodeTreeView\AbstractNodeTreeView;
use wcf\system\request\LinkHandler;

/**
 * Node tree view that shows the items of a menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class MenuItemNodeTreeView extends AbstractNodeTreeView
{
    public function __construct(public readonly int $menuID)
    {
        $provider = new MenuItemInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(MenuItemEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/menus/items/%s/enable',
                'core/menus/items/%s/disable'
            )
        );
    }

    #[\Override]
    public function getNodes(): \RecursiveIteratorIterator
    {
        $nodeTree = new MenuItemNodeTree($this->menuID, null, false);

        return $nodeTree->getNodeList();
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['menuID' => $this->menuID];
    }

    #[\Override]
    public function getNodeLink(IObjectTreeNode $node): string
    {
        \assert($node instanceof MenuItemNode);

        return LinkHandler::getInstance()->getControllerLink(
            MenuItemEditForm::class,
            ['id' => $node->getObjectID()]
        );
    }
}
