<?php

namespace wcf\data\menu;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of menus.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<Menu>
 */
class MenuList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Menu::class;
}
