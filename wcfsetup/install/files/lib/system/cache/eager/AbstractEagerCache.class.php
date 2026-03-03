<?php

namespace wcf\system\cache\eager;

use wcf\system\cache\CacheHandler;
use wcf\util\ClassUtil;

/**
 * Eager caches are caches that do not expire and must be renewed manually if the data in the cache has changed.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @template T of array|object
 */
abstract class AbstractEagerCache
{
    /**
     * @var array<string, T>
     */
    private static array $caches = [];
    private string $cacheName;

    /**
     * Returns the cache.
     *
     * @return T
     */
    final public function getCache(): array|object
    {
        $key = $this->getCacheKey();

        if (!\array_key_exists($key, AbstractEagerCache::$caches)) {
            $cache = CacheHandler::getInstance()->getCacheSource()->get($key, 0);
            if ($cache === null) {
                $this->rebuild();
            } else {
                AbstractEagerCache::$caches[$key] = $cache;
            }
        }

        return AbstractEagerCache::$caches[$key];
    }

    private function getCacheKey(): string
    {
        if (!isset($this->cacheName)) {
            /* @see CacheHandler::getCacheName() */
            $this->cacheName = \str_replace(
                ['\\', 'system_cache_eager_'],
                ['_', ''],
                \get_class($this)
            );

            $parameters = ClassUtil::getConstructorProperties($this);

            if ($parameters !== []) {
                $this->cacheName .= '-' . CacheHandler::getInstance()->getCacheIndex($parameters);
            }
        }

        return $this->cacheName;
    }

    /**
     * Rebuilds the cache data and stores the updated data.
     */
    final public function rebuild(): void
    {
        $key = $this->getCacheKey();
        $newCacheData = $this->getCacheData();

        // The existing cache must not be overwritten, otherwise this can cause errors at runtime.
        // The new data will be available at the next request.
        if (!\array_key_exists($key, AbstractEagerCache::$caches)) {
            AbstractEagerCache::$caches[$key] = $newCacheData;
        }

        CacheHandler::getInstance()->getCacheSource()->set($key, $newCacheData, 0);
    }

    /**
     * Generates the cache data and returns it.
     * This method MUST NOT rely on any (runtime) cache at any point because those could be stale.
     *
     * @return T
     */
    abstract protected function getCacheData(): array|object;
}
