<?php

namespace wcf\data\label;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit labels.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Label   create(array $parameters = [])
 * @method      Label   getDecoratedObject()
 * @mixin       Label
 */
class LabelEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Label::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        LabelCacheBuilder::getInstance()->reset();
    }

    /**
     * Adds the label to a specific position in the label group.
     *
     * @param int $groupID
     * @param int $showOrder
     */
    public function setShowOrder($groupID, $showOrder = 0)
    {
        // shift back labels in old label group with higher showOrder
        if ($this->showOrder) {
            $sql = "UPDATE  wcf1_label
                    SET     showOrder = showOrder - 1
                    WHERE   groupID = ?
                        AND showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->groupID, $this->showOrder]);
        }

        // shift labels in new label group with higher showOrder
        if ($showOrder) {
            $sql = "UPDATE  wcf1_label
                    SET     showOrder = showOrder + 1
                    WHERE   groupID = ?
                        AND showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$groupID, $showOrder]);
        }

        // get maximum existing show order
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_label
                WHERE   groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$groupID]);
        $maxShowOrder = $statement->fetchSingleColumn() ?: 0;

        if (!$showOrder || $showOrder > $maxShowOrder) {
            $showOrder = $maxShowOrder + 1;
        }

        $this->update(['showOrder' => $showOrder]);
    }
}
