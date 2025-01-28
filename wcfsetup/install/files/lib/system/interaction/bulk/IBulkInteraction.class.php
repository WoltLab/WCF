<?php

namespace wcf\system\interaction\bulk;

use wcf\data\DatabaseObject;

/**
 * Represents a bulk interaction that can be applied DatabaseObjects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IBulkInteraction
{
    /**
     * Renders the interaction for the given objects.
     * @param DatabaseObject[] $objects
     */
    public function render(array $objects): string;

    /**
     * Renders the initialization code for this interaction.
     */
    public function renderInitialization(string $containerId): ?string;

    /**
     * Returns true if this interaction is available for the given object
     */
    public function isAvailable(DatabaseObject $object): bool;

    /**
     * Returns the identifier of this interaction.
     */
    public function getIdentifier(): string;
}
