<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\CoreObjectCache;

/**
 * Caches the core objects.
 *
 * @author Olaf Braun, Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `CoreObjectCache` instead
 */
class CoreObjectCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function reset(array $parameters = [])
    {
        new CoreObjectCache()->rebuild();
    }

    #[\Override]
    public function rebuild(array $parameters): array
    {
        return new CoreObjectCache()->getCache();
    }
}
