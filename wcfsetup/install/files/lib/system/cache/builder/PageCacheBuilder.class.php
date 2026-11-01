<?php

namespace wcf\system\cache\builder;

use wcf\data\page\PageCache;
use wcf\system\cache\eager\PageRowCache;

/**
 * Caches the page data.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.3 use `PageCache` instead
 */
class PageCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters): array
    {
        $cache = new PageRowCache()->getCache();
        $pageCache = PageCache::getInstance();

        return [
            'identifier' => $cache['identifier'],
            'controller' => $cache['controller'],
            'pages' => $pageCache->getPages(),
            'pageTitles' => $cache['pageTitles'],
            'landingPage' => $pageCache->getLandingPage(),
            'pageMetaDescriptions' => $cache['pageMetaDescriptions'],
        ];
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new PageRowCache()->rebuild();
    }
}
