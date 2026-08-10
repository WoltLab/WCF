<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Provides a backwards compatible interface for ICacheBuilder classes that have been migrated to the eager or tolerant cache implementations.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @since 6.2
 * @deprecated 6.2
 */
abstract class AbstractLegacyCacheBuilder extends SingletonFactory implements ICacheBuilder
{
    /**
     * @var array<string, array<string|int, mixed>>
     */
    private array $cache = [];

    #[\Override]
    public function getData(array $parameters = [], string $arrayIndex = '')
    {
        $index = CacheHandler::getInstance()->getCacheIndex($parameters);
        if (isset($this->cache[$index])) {
            $cache = $this->cache[$index];
        } else {
            $cache = $this->rebuild($parameters);
            $this->cache[$index] = $cache;
        }

        if ($arrayIndex !== '') {
            if (!\array_key_exists($arrayIndex, $cache)) {
                throw new SystemException("array index '" . $arrayIndex . "' does not exist in cache resource");
            }

            return $cache[$arrayIndex];
        }

        return $cache;
    }

    /**
     * Rebuilds cache for current resource.
     *
     * @param mixed[] $parameters
     * @return mixed[]
     */
    abstract protected function rebuild(array $parameters): array;

    #[\Override]
    final public function getMaxLifetime()
    {
        return 0;
    }
}
