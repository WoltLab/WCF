<?php

namespace wcf\data\acp\menu\item;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ACPMenuCacheBuilder;

/**
 * Provides functions to edit ACP menu items.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       ACPMenuItem
 * @extends DatabaseObjectEditor<ACPMenuItem>
 * @implements IEditableCachedObject<ACPMenuItem>
 * @deprecated 6.3
 */
class ACPMenuItemEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ACPMenuItem::class;

    #[\Override]
    public static function resetCache()
    {
        ACPMenuCacheBuilder::getInstance()->reset();
    }
}
