<?php

namespace wcf\data\user\group\option;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;

/**
 * Provides functions to edit usergroup options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       UserGroupOption
 * @extends DatabaseObjectEditor<UserGroupOption>
 * @implements IEditableCachedObject<UserGroupOption>
 */
class UserGroupOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserGroupOption::class;

    #[\Override]
    public static function resetCache()
    {
        UserGroupOptionCacheBuilder::getInstance()->reset();
    }
}
