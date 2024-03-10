<?php

namespace wcf\data\menu;

use wcf\data\box\Box;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\system\WCF;

/**
 * Represents a menu.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   int $menuID         unique id of the menu
 * @property-read   string $identifier     textual identifier of the menu
 * @property-read   string $title          title of the menu or name of language item which contains the title
 * @property-read   int $originIsSystem     is `1` if the menu has been delivered by a package, otherwise `0` (if the menu has been created by an admin in the ACP)
 * @property-read   int $packageID      id of the package the which delivers the menu or `1` if it has been created in the ACP
 */
class Menu extends DatabaseObject implements ITitledObject
{
    /**
     * menu item node tree
     * @var MenuItemNodeTree
     */
    protected $menuItemNodeTree;

    /**
     * box object
     * @var Box
     */
    protected $box;

    /**
     * Returns true if the active user can delete this menu.
     *
     * @return  bool
     */
    public function canDelete()
    {
        if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu') && !$this->originIsSystem) {
            return true;
        }

        return false;
    }

    /**
     * Returns the items of this menu.
     *
     * @return  \RecursiveIteratorIterator
     */
    public function getMenuItemNodeList()
    {
        return $this->getMenuItemNodeTree()->getNodeList();
    }

    /**
     * Returns false if this menu has no content (has menu items).
     *
     * @return  bool
     */
    public function hasContent()
    {
        return $this->getMenuItemNodeTree()->getVisibleItemCount() > 0;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns the content for the rendered version of this menu.
     *
     * @return  string
     */
    public function getContent()
    {
        WCF::getTPL()->assign([
            'menuItemNodeList' => $this->getMenuItemNodeList(),
            'menuTitle' => $this->getTitle(),
            'menuIdentifier' => $this->identifier,
        ]);

        return WCF::getTPL()->fetch('__menu');
    }

    /**
     * Returns the box of this menu.
     *
     * @return  Box
     */
    public function getBox()
    {
        if ($this->box === null) {
            $this->box = Box::getBoxByMenuID($this->menuID);
        }

        return $this->box;
    }

    /**
     * Returns the menu item node tree with the menu's items.
     *
     * @return  MenuItemNodeTree
     */
    protected function getMenuItemNodeTree()
    {
        if ($this->menuItemNodeTree === null) {
            $this->menuItemNodeTree = new MenuItemNodeTree(
                $this->menuID,
                MenuCache::getInstance()->getMenuItemsByMenuID($this->menuID)
            );
        }

        return $this->menuItemNodeTree;
    }
}
