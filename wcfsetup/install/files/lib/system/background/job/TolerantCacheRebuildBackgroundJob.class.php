<?php

namespace wcf\system\background\job;

use wcf\system\cache\CacheHandler;
use wcf\system\cache\tolerant\AbstractTolerantCache;

/**
 * Rebuilds the cache data of a tolerant cache.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class TolerantCacheRebuildBackgroundJob extends AbstractUniqueBackgroundJob
{
    /**
     * @param class-string<AbstractTolerantCache<mixed[]|object>> $cacheClass
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string $cacheClass,
        public readonly array $parameters = []
    ) {}

    #[\Override]
    public function identifier(): string
    {
        $identifier = $this->cacheClass;
        if ($this->parameters !== []) {
            $identifier .= '-' . CacheHandler::getInstance()->getCacheIndex($this->parameters);
        }

        return $identifier;
    }

    #[\Override]
    public function newInstance(): static
    {
        return new TolerantCacheRebuildBackgroundJob($this->cacheClass, $this->parameters);
    }

    #[\Override]
    public function queueAgain(): bool
    {
        return false;
    }

    #[\Override]
    public function perform()
    {
        if (!\class_exists($this->cacheClass)) {
            return;
        }

        $asyncCache = new $this->cacheClass(...$this->parameters);
        $asyncCache->rebuild();
    }
}
