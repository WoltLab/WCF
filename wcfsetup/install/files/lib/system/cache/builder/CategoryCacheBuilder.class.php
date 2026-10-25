<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\CategoryCache;

/**
 * Caches the categories for the active application.
 *
 * @author Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `CategoryCache` instead
 */
class CategoryCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        $cache = new CategoryCache()->getCache();

        return [
            'categories' => $cache->categories,
            'objectTypeCategoryIDs' => $cache->objectTypeCategoryIDs,
        ];
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new CategoryCache()->rebuild();
    }
}
