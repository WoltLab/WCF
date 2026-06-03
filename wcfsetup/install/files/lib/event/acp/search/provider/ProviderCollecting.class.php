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

    /**
     * @var array<string, int>
     */
    private array $showOrders = [];

    public function register(string $providerName, IACPSearchResultProvider $provider, int $showOrder = 0): void
    {
        $this->providers[$providerName] = $provider;
        $this->showOrders[$providerName] = $showOrder;
    }

    /**
     * @return array<string, IACPSearchResultProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getShowOrder(string $providerName): int
    {
        return $this->showOrders[$providerName] ?? 0;
    }
}
