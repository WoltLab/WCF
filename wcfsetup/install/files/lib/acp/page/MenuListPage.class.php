<?php

namespace wcf\acp\page;

use wcf\data\menu\MenuList;
use wcf\page\SortablePage;

/**
 * Shows a list of menus.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property    MenuList $objectList
 */
class MenuListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';

    /**
     * @inheritDoc
     */
    public $objectListClassName = MenuList::class;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMenu'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'title';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['menuID', 'title', 'position', 'items', 'showOrder'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects .= '(
            SELECT  COUNT(*)
            FROM    wcf1_menu_item
            WHERE   menuID = menu.menuID
        ) AS items, (
            SELECT  position
            FROM    wcf1_box
            WHERE   menuID = menu.menuID
        ) AS position, (
            SELECT  showOrder
            FROM    wcf1_box
            WHERE   menuID = menu.menuID
        ) AS showOrder';
    }
}
