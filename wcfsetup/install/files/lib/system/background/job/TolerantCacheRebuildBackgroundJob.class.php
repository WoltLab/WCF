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
    public function __construct(
        /** @var class-string<AbstractTolerantCache<array|object>> */
        public readonly string $cacheClass,
        /** @var array<string, mixed> */
        public readonly array $parameters = []
    ) {}

    public function identifier(): string
    {
        $identifier = $this->cacheClass;
        if (!empty($this->parameters)) {
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
        \assert($asyncCache instanceof AbstractTolerantCache);

        $asyncCache->rebuild();
    }
}
