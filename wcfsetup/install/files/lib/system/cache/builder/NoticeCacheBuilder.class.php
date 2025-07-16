<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\NoticeCache;

/**
 * Caches the enabled notices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `NoticeCache` instead
 */
final class NoticeCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        return (new NoticeCache())->getCache();
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        (new NoticeCache())->rebuild();
    }
}
