<?php

namespace wcf\system\stat;

use wcf\system\WCF;

/**
 * Stat handler implementation for like stats.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Stat
 */
class LikeStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_like
                WHERE   time BETWEEN ? AND ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$date, $date + 86399]);
        $counter = (int)$statement->fetchSingleColumn();

        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_like
                WHERE   time < ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$date + 86400]);
        $total = (int)$statement->fetchSingleColumn();

        return [
            'counter' => $counter,
            'total' => $total,
        ];
    }
}
