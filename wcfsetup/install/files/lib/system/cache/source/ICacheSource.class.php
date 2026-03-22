<?php

namespace wcf\system\cache\source;

/**
 * Any cache sources should implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ICacheSource
{
    /**
     * Flushes a specific cache, optionally removing caches which share the same name.
     *
     * @return void
     */
    public function flush(string $cacheName, bool $useWildcard);

    /**
     * Clears the cache completely.
     *
     * @return void
     */
    public function flushAll();

    /**
     * Returns a cached variable.
     *
     * @return mixed
     */
    public function get(string $cacheName, int $maxLifetime);

    /**
     * Stores a variable in the cache.
     *
     * @return void
     */
    public function set(string $cacheName, mixed $value, int $maxLifetime);

    /**
     * Returns the timestamp when the cache was created.
     * Or `null` if the cache does not exist or is empty.
     */
    public function getCreationTime(string $cacheName, int $maxLifetime): ?int;
}
