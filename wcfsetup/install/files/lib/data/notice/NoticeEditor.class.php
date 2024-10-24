<?php

namespace wcf\data\notice;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\builder\NoticeCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Notice      create(array $parameters = [])
 * @method      Notice      getDecoratedObject()
 * @mixin       Notice
 */
class NoticeEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Notice::class;

    /**
     * Sets the show order of the notice.
     *
     * @param int $showOrder
     */
    public function setShowOrder($showOrder = 0)
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_notice";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $maxShowOrder = $statement->fetchSingleColumn();
        if (!$maxShowOrder) {
            $maxShowOrder = 0;
        }

        if (!$showOrder || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            // shift other notices
            $sql = "UPDATE  wcf1_notice
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $showOrder,
            ]);

            $newShowOrder = $showOrder;
        }

        $this->update(['showOrder' => $newShowOrder]);
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        NoticeCacheBuilder::getInstance()->reset();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.notice')
                ->definitionID,
        ]);
    }
}
