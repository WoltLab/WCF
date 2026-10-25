<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\tolerant\TagCloudCache;

/**
 * Caches the typed tag cloud.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `TagCloudCache` instead
 */
class TypedTagCloudCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function reset(array $parameters = [])
    {
        new TagCloudCache($parameters["objectTypeIDs"], $parameters["languageIDs"])->rebuild();
    }

    #[\Override]
    protected function rebuild(array $parameters): array
    {
        return new TagCloudCache($parameters["objectTypeIDs"], $parameters["languageIDs"])->getCache();
    }
}
