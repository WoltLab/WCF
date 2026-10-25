<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\tolerant\UserStatsCache;

/**
 * Caches the number of members and the newest member.
 *
 * @author Olaf Braun, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `UserStatsCache` instead
 */
class UserStatsCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        $cache = new UserStatsCache()->getCache();

        return [
            'members' => $cache->members,
            'newestMember' => $cache->newestMember
        ];
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new UserStatsCache()->rebuild();
    }
}
