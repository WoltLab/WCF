<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;

/**
 * Represents an interaction that can be applied to a DatabaseObject.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IInteraction
{
    /**
     * Renders the interaction for the given object.
     */
    public function render(DatabaseObject $object): string;

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
