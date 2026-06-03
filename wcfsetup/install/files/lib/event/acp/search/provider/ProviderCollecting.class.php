<?php

namespace wcf\event\acp\search\provider;

use wcf\event\IPsr14Event;
use wcf\system\search\acp\IACPSearchResultProvider;

/**
 * Requests the collection of acp search providers.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ProviderCollecting implements IPsr14Event
{
    /**
     * @var array<string, IACPSearchResultProvider>
     */
    private array $providers = [];

    public function register(string $providerName, IACPSearchResultProvider $provider): void
    {
        $this->providers[$providerName] = $provider;
    }

    /**
     * @return array<string, IACPSearchResultProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
