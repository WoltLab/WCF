<?php

namespace wcf\system\interaction\bulk;

use wcf\data\DatabaseObject;

/**
 * Provides an abstract implementation of a bulk interaction that can be applied DatabaseObjects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractBulkInteraction implements IBulkInteraction
{
    public function __construct(
        protected readonly string $identifier,
        protected readonly ?\Closure $isAvailableCallback = null
    ) {}

    #[\Override]
    public function isAvailable(DatabaseObject $object): bool
    {
        if ($this->isAvailableCallback === null) {
            return true;
        }

        return ($this->isAvailableCallback)($object);
    }

    #[\Override]
    public function renderInitialization(string $containerId): ?string
    {
        return null;
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
