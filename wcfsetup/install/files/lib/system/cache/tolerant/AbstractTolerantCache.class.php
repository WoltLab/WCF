<?php

namespace wcf\system\cache\tolerant;

use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\TolerantCacheRebuildBackgroundJob;
use wcf\system\cache\CacheHandler;
use wcf\util\ClassUtil;

/**
 * Tolerant caches are caches that are rebuilt in the background when they are about to expire or have already expired.
 * The cache data can be outdated, this must not be a problem when using these caches.
 * The lifetime MUST BE `>= 300`.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @template T of array|object
 */
abstract class AbstractTolerantCache
{
    /**
     * @var T
     */
    private array|object $cache;
    private string $cacheName;

    /**
     * @return T
     */
    final public function getCache(): array|object
    {
        if (!isset($this->cache)) {
            $cache = CacheHandler::getInstance()->getCacheSource()->get(
                $this->getCacheKey(),
                0,
            );

            if ($cache === null) {
                $this->rebuild();
            } else {
                $this->cache = $cache;
            }

            if ($this->needsRebuild()) {
                BackgroundQueueHandler::getInstance()->enqueueIn([
                    new TolerantCacheRebuildBackgroundJob(
                        \get_class($this),
                        ClassUtil::getConstructorProperties($this)
                    )
                ]);
                BackgroundQueueHandler::getInstance()->forceCheck();
            }
        }
        return $this->cache;
    }

    private function getCacheKey(): string
    {
        if (!isset($this->cacheName)) {
            /* @see AbstractEagerCache::getCacheKey() */
            $this->cacheName = \str_replace(
                ['\\', 'system_cache_tolerant_'],
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

    final public function rebuild(): void
    {
        $newCacheData = $this->rebuildCacheData();

        if (!isset($this->cache)) {
            $this->cache = $newCacheData;
        }

        CacheHandler::getInstance()->getCacheSource()->set(
            $this->getCacheKey(),
            $newCacheData,
            0
        );
    }

    /**
     * @return T
     */
    abstract protected function rebuildCacheData(): array|object;

    final public function nextRebuildTime(): int
    {
        $lifetime = $this->getLifetime();
        \assert($lifetime >= 300);

        $cacheTime = CacheHandler::getInstance()->getCacheSource()->getCreationTime(
            $this->getCacheKey(),
            $lifetime
        );

        if ($cacheTime === null) {
            return \TIME_NOW;
        }

        return $cacheTime + $lifetime;
    }

    /**
     * Return the lifetime of the cache in seconds.
     */
    abstract public function getLifetime(): int;

    private function needsRebuild(): bool
    {
        // Probabilistic early expiration
        // https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration

        return \TIME_NOW - 10 * \log(\random_int(1, \PHP_INT_MAX) / \PHP_INT_MAX)
            >= $this->nextRebuildTime();
    }
}
