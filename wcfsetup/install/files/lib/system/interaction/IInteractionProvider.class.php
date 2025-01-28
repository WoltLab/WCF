<?php

namespace wcf\system\interaction;

/**
 * Represents a provider that provides interactions that can be applied to a specific type of DatabaseObject.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface IInteractionProvider
{
    /**
     * Returns the interactions provided by this provider.
     * @return (IInteraction|Divider)[]
     */
    public function getInteractions(): array;

    /**
     * Adds the given interaction to the provider.
     */
    public function addInteraction(IInteraction|Divider $interaction): void;

    /**
     * Adds the given interactions to the provider.
     * @param (IInteraction|Divider)[] $interactions
     */
    public function addInteractions(array $interactions): void;

    /**
     * Adds a new interaction at the position before the given id.
     */
    public function addInteractionBefore(IInteraction|Divider $interaction, string $beforeID): void;

    /**
     * Adds a new interaction at the position after the given id.
     */
    public function addInteractionAfter(IInteraction|Divider $interaction, string $afterID): void;

    /**
     * Returns the class name of the object that the interactions can be applied to.
     */
    public function getObjectClassName(): string;
}
