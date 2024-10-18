<?php

namespace wcf\data\ad;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\AdCacheBuilder;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit ads.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Ad  create(array $parameters = [])
 * @method      Ad  getDecoratedObject()
 * @mixin       Ad
 */
class AdEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Ad::class;

    /**
     * Sets the show order of the ad.
     *
     * @param int $showOrder
     */
    public function setShowOrder($showOrder = 0)
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_ad
                WHERE   objectTypeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->objectTypeID,
        ]);
        $maxShowOrder = $statement->fetchSingleColumn();
        if (!$maxShowOrder) {
            $maxShowOrder = 0;
        }

        if (!$showOrder || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            // shift other ads
            $sql = "UPDATE  wcf1_ad
                    SET     showOrder = showOrder + 1
                    WHERE   objectTypeID = ?
                            AND showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->objectTypeID,
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
        AdCacheBuilder::getInstance()->reset();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.ad')
                ->definitionID,
        ]);
    }
}
