<?php

namespace wcf\data\label\group;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit label groups.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       LabelGroup
 * @extends DatabaseObjectEditor<LabelGroup>
 * @implements IEditableCachedObject<LabelGroup>
 * @deprecated 6.3 use `LabelGroupBuilder` instead.
 */
class LabelGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = LabelGroup::class;

    #[\Override]
    public static function deleteAll(array $objectIDs = [])
    {
        if ($objectIDs === []) {
            return 0;
        }

        // See `DeleteLabelGroup` for the transaction.
        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            LabelGroupBuilder::deleteAll(\array_values($objectIDs));

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }

        return \count($objectIDs);
    }

    #[\Override]
    public static function resetCache()
    {
        LabelCacheBuilder::getInstance()->reset();
    }
}
