<?php

namespace wcf\system\stat;

use wcf\system\WCF;

/**
 * Stat handler implementation for like stats.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LikeStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_like
                WHERE   time BETWEEN ? AND ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$date, $date + 86399]);
        $counter = \intval($statement->fetchSingleColumn());

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_like
                WHERE   time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$date + 86400]);
        $total = \intval($statement->fetchSingleColumn());

        return [
            'counter' => $counter,
            'total' => $total,
        ];
    }
}
