<?php

namespace wcf\data\notice;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\eager\NoticeCache;
use wcf\system\WCF;

/**
 * Provides functions to edit notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Notice
 * @extends DatabaseObjectEditor<Notice>
 * @implements IEditableCachedObject<Notice>
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
     * @return void
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
        (new NoticeCache())->rebuild();
    }
}
