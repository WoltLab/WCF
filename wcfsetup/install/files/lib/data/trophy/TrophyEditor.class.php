<?php

namespace wcf\data\trophy;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\WCF;

/**
 * A trophy editor.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @mixin   Trophy
 */
class TrophyEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Trophy::class;

    /**
     * Sets the show order of the trophy.
     *
     * @param int $showOrder
     */
    public function setShowOrder($showOrder = 0)
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_trophy";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $maxShowOrder = $statement->fetchSingleColumn();
        if (!$maxShowOrder) {
            $maxShowOrder = 0;
        }

        if (!$showOrder || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            // shift other trophies
            $sql = "UPDATE  wcf1_trophy
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $showOrder,
            ]);

            $newShowOrder = $showOrder;
        }

        $this->update([
            'showOrder' => $newShowOrder,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        TrophyCache::getInstance()->clearCache();
    }
}
