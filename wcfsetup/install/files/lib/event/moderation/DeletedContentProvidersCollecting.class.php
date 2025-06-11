<?php

namespace wcf\event\moderation;

use wcf\data\object\type\ObjectTypeCache;
use wcf\event\IPsr14Event;
use wcf\system\moderation\IDeletedContentListViewProvider;
use wcf\system\moderation\IDeletedContentProvider;

/**
 * Requests the collection of deleted content providers.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class DeletedContentProvidersCollecting implements IPsr14Event
{
    /**
     * @var list<IDeletedContentProvider | IDeletedContentListViewProvider>
     */
    private array $providers = [];

    public function __construct()
    {
        $this->loadLegacyProviders();
    }

    public function register(IDeletedContentListViewProvider $provider): void
    {
        $this->providers[$provider->getIdentifier()] = $provider;
    }

    /**
     * @return list<IDeletedContentProvider | IDeletedContentListViewProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @deprecated 6.2
     */
    private function loadLegacyProviders(): void
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.deletedContent');
        foreach ($objectTypes as $objectType) {
            $this->providers[$objectType->objectType] = $objectType->getProcessor();
        }
    }
}
