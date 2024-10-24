<?php

namespace wcf\system\cache\builder;

use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Caches user birthdays (one cache file per month).
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserBirthdayCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 3600;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $userOptionID = User::getUserOptionID('birthday');
        if ($userOptionID === null) {
            // birthday profile field missing; skip
            return [];
        }

        $data = [];
        $birthday = 'userOption' . $userOptionID;
        $sql = "SELECT  userID, " . $birthday . "
                FROM    wcf1_user_option_value
                WHERE   " . $birthday . " LIKE ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['%-' . ($parameters['month'] < 10 ? '0' : '') . $parameters['month'] . '-%']);
        while ($row = $statement->fetchArray()) {
            [, $month, $day] = \explode('-', $row[$birthday]);
            if (!isset($data[$month . '-' . $day])) {
                $data[$month . '-' . $day] = [];
            }
            $data[$month . '-' . $day][] = $row['userID'];
        }

        return $data;
    }
}
