<?php

namespace wcf\event\message\mention;

use wcf\event\IPsr14Event;
use wcf\system\message\mention\IMentionSuggestionProvider;

/**
 * Requests the collection of providers for context-aware mention suggestions.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MentionSuggestionProviderCollecting implements IPsr14Event
{
    /**
     * @var array<string, IMentionSuggestionProvider>
     */
    private array $providers = [];

    public function register(string $objectType, IMentionSuggestionProvider $provider): void
    {
        $this->providers[$objectType] = $provider;
    }

    /**
     * @return array<string, IMentionSuggestionProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
