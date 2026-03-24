<?php

namespace wcf\data\condition;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ConditionCacheBuilder;

/**
 * Executes condition-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Condition
 * @extends DatabaseObjectEditor<Condition>
 * @implements IEditableCachedObject<Condition>
 */
class ConditionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Condition::class;

    #[\Override]
    public static function resetCache()
    {
        ConditionCacheBuilder::getInstance()->reset();
    }
}
