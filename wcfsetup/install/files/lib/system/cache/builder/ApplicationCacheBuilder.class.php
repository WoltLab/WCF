<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\ApplicationCache;

/**
 * Caches applications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.3 Use `ApplicationCache` instead.
 */
class ApplicationCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        $cache = (new ApplicationCache())->getCache();

        return [
            'application' => $cache->application,
            'abbreviation' => $cache->abbreviation,
        ];
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        (new ApplicationCache())->rebuild();
    }
}
