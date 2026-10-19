<?php

namespace wcf\data\acp\menu\item;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP menu items.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<ACPMenuItem>
 * @deprecated 6.3
 */
class ACPMenuItemList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ACPMenuItem::class;
}
