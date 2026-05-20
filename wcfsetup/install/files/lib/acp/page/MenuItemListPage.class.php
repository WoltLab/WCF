<?php

namespace wcf\acp\page;

use wcf\data\menu\Menu;
use wcf\http\Helper;
use wcf\page\AbstractNodeTreeViewPage;
use wcf\system\nodeTreeView\admin\MenuItemNodeTreeView;
use wcf\system\WCF;

/**
 * Shows a list of menu items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractNodeTreeViewPage<MenuItemNodeTreeView>
 */
class MenuItemListPage extends AbstractNodeTreeViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMenu'];

    public Menu $menu;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->menu = Helper::fetchObjectFromQueryParameter(Menu::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'menu' => $this->menu,
            'menuID' => $this->menu->getObjectID(),
        ]);
    }

    #[\Override]
    protected function createNodeTreeView(): MenuItemNodeTreeView
    {
        return new MenuItemNodeTreeView($this->menu->getObjectID());
    }

    #[\Override]
    protected function getBaseUrlParameters(): array
    {
        return ['id' => $this->menu->getObjectID()];
    }
}
