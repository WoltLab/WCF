<?php

namespace wcf\system\cache\builder;

use FastRoute\Cache;

/**
 * Caches the list of available API endpoints.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class ApiEndpointCacheBuilder extends AbstractCacheBuilder implements Cache
{
    private \Closure $loader;

    #[\Override]
    public function get(string $key, callable $loader): array
    {
        $this->loader = \Closure::fromCallable($loader);

        return $this->getData(['key' => $key]);
    }

    #[\Override]
    public function rebuild(array $parameters)
    {
        if (!isset($this->loader)) {
            throw new \RuntimeException('You may not access the API endpoint cache builder directly.');
        }

        $callable = $this->loader;

        return $callable();
    }
}
