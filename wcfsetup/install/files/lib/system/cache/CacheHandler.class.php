<?php

namespace wcf\system\cache;

use wcf\system\cache\builder\ICacheBuilder;
use wcf\system\cache\source\DiskCacheSource;
use wcf\system\SingletonFactory;

/**
 * Manages transparent cache access.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CacheHandler extends SingletonFactory
{
    protected DiskCacheSource $cacheSource;

    #[\Override]
    protected function init(): void
    {
        $this->cacheSource = new DiskCacheSource();
    }

    /**
     * Flush cache for given resource.
     *
     * @param mixed[] $parameters
     */
    public function flush(ICacheBuilder $cacheBuilder, array $parameters): void
    {
        $this->getCacheSource()->flush($this->getCacheName($cacheBuilder, $parameters), empty($parameters));
    }

    /**
     * Flushes the entire cache.
     */
    public function flushAll(): void
    {
        $this->getCacheSource()->flushAll();
    }

    /**
     * Returns cached value for given resource, false if no cache exists.
     *
     * @param mixed[] $parameters
     */
    public function get(ICacheBuilder $cacheBuilder, array $parameters): mixed
    {
        return $this->getCacheSource()->get(
            $this->getCacheName($cacheBuilder, $parameters),
            $cacheBuilder->getMaxLifetime()
        );
    }

    /**
     * Caches a value for given resource.
     *
     * @param mixed[] $parameters
     * @param mixed[] $data
     */
    public function set(ICacheBuilder $cacheBuilder, array $parameters, array $data): void
    {
        $this->getCacheSource()->set(
            $this->getCacheName($cacheBuilder, $parameters),
            $data,
            $cacheBuilder->getMaxLifetime()
        );
    }

    /**
     * Returns cache index hash.
     *
     * @param mixed[] $parameters
     */
    public function getCacheIndex(array $parameters): string
    {
        return \sha1(\serialize($this->orderParameters($parameters)));
    }

    /**
     * Builds cache name.
     *
     * @param mixed[] $parameters
     */
    protected function getCacheName(ICacheBuilder $cacheBuilder, array $parameters = []): string
    {
        $cacheName = \str_replace(
            ['\\', 'system_cache_builder_'],
            ['_', ''],
            \get_class($cacheBuilder)
        );
        if (!empty($parameters)) {
            $cacheName .= '-' . $this->getCacheIndex($parameters);
        }

        return $cacheName;
    }

    /**
     * Returns the cache source object.
     */
    public function getCacheSource(): DiskCacheSource
    {
        return $this->cacheSource;
    }

    /**
     * Unifies parameter order, numeric indices will be discarded.
     *
     * @param mixed[] $parameters
     * @return mixed[]
     */
    protected function orderParameters(array $parameters): array
    {
        if (!empty($parameters)) {
            \array_multisort($parameters);
        }

        return $parameters;
    }
}
