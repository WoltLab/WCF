<?php

namespace wcf\system\interaction\bulk;

use wcf\system\interaction\Divider;

/**
 * Represents a provider that provides bulk interactions that can be applied to a specific type of DatabaseObjects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IBulkInteractionProvider
{
    /**
     * Returns the interactions provided by this provider.
     * @return (IBulkInteraction|Divider)[]
     */
    public function getInteractions(): array;

    /**
     * Adds the given interaction to the provider.
     */
    public function addInteraction(IBulkInteraction|Divider $interaction): void;

    /**
     * Adds the given interactions to the provider.
     * @param (IBulkInteraction|Divider)[] $interactions
     */
    public function addInteractions(array $interactions): void;

    /**
     * Adds a new interaction at the position before the given id.
     */
    public function addInteractionBefore(IBulkInteraction|Divider $interaction, string $beforeID): void;

    /**
     * Adds a new interaction at the position after the given id.
     */
    public function addInteractionAfter(IBulkInteraction|Divider $interaction, string $afterID): void;

    /**
     * Returns the class name of the object list that the interactions can be applied to.
     */
    public function getObjectListClassName(): string;
}
