<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\tolerant\UserBirthdayCache;

/**
 * Caches user birthdays (one cache file per month).
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `UserBirthdayCache` instead
 */
class UserBirthdayCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        $cache = [];
        foreach (new UserBirthdayCache($parameters['month'])->getCache() as $day => $userIDs) {
            $cache[\sprintf("%02d-%02d", $parameters['month'], $day)] = $userIDs;
        }

        return $cache;
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new UserBirthdayCache($parameters['month'])->rebuild();
    }
}
