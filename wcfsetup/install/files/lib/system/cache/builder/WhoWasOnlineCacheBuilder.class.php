<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\tolerant\WhoWasOnlineCache;

/**
 * Caches a list of users that visited the website in last 24 hours.
 *
 * @author  Olaf Braun, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `WhoWasOnlineCache` instead
 */
class WhoWasOnlineCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        return new WhoWasOnlineCache()->getCache();
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new WhoWasOnlineCache()->rebuild();
    }
}
