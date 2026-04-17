<?php

namespace wcf\system\cache\tolerant;

use wcf\system\WCF;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractTolerantCache<list<int>>
 */
final class WhoWasOnlineCache extends AbstractTolerantCache
{
    #[\Override]
    public function getLifetime(): int
    {
        return 600;
    }

    #[\Override]
    protected function rebuildCacheData(): array
    {
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
        $statement->execute([\TIME_NOW - 86400, \TIME_NOW - USER_ONLINE_TIMEOUT]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
