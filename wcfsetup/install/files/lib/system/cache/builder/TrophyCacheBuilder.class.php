<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\TrophyCache;

/**
 * Caches the trophies.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @deprecated since 6.3, use `wcf\system\cache\eager\TrophyCache` instead.
 */
class TrophyCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        $cache = (new TrophyCache())->getCache();

        if (isset($parameters['onlyEnabled']) && $parameters['onlyEnabled']) {
            return $cache->enabledTrophies;
        }

        return $cache->trophies;
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        (new TrophyCache())->rebuild();
    }
}
