<?php

namespace wcf\system\cache\builder;

use wcf\system\WCF;

/**
 * Caches a list of users that visited the website in last 24 hours.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class WhoWasOnlineCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 600;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $userIDs = [];
        $sql = "(
                    SELECT  userID
                    FROM    wcf1_user
                    WHERE   lastActivityTime > ?
                ) UNION (
                    SELECT  userID
                    FROM    wcf1_session
                    WHERE   userID IS NOT NULL
                        AND lastActivityTime > ?
                )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([TIME_NOW - 86400, TIME_NOW - USER_ONLINE_TIMEOUT]);
        while ($userID = $statement->fetchColumn()) {
            $userIDs[] = $userID;
        }

        return $userIDs;
    }
}
