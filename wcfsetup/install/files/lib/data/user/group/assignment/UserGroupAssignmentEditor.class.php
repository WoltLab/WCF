<?php

namespace wcf\data\user\group\assignment;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;

/**
 * Executes user group assignment-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Group\Assignment
 *
 * @method static   UserGroupAssignment create(array $parameters = [])
 * @method      UserGroupAssignment getDecoratedObject()
 * @mixin       UserGroupAssignment
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
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.condition.userGroupAssignment')->definitionID,
        ]);
    }
}
