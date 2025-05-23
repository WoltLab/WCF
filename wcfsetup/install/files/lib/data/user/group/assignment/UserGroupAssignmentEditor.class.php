<?php

namespace wcf\data\user\group\assignment;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;

/**
 * Executes user group assignment-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       UserGroupAssignment
 * @extends DatabaseObjectEditor<UserGroupAssignment>
 * @implements IEditableCachedObject<UserGroupAssignment>
 */
class UserGroupAssignmentEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserGroupAssignment::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        UserGroupAssignmentCacheBuilder::getInstance()->reset();
    }
}
