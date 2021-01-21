<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Caches the number of members and the newest member.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cache\Builder
 */
class UserStatsCacheBuilder extends AbstractCacheBuilder
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
        $data = [];

        // number of members
        $sql = "SELECT  COUNT(*) AS amount
                FROM    wcf" . WCF_N . "_user";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $data['members'] = $statement->fetchColumn();

        // newest member
        $sql = "SELECT      userID
                FROM        wcf" . WCF_N . "_user
                ORDER BY    userID DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute();
        $data['newestMember'] = UserProfileRuntimeCache::getInstance()->getObject($statement->fetchSingleColumn());

        return $data;
    }
}
