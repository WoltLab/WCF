<?php

namespace wcf\system\interaction;

/**
 * Provides an abstract implementation of a provider that provides interactions
 * that can be applied to a specific type of DatabaseObject.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractInteractionProvider implements IInteractionProvider
{
    /**
     * @var (IInteraction|Divider)[]
     */
    private array $interactions = [];

    #[\Override]
    public function getInteractions(): array
    {
        return $this->interactions;
    }

    #[\Override]
    public function addInteraction(IInteraction|Divider $interaction): void
    {
        $this->interactions[] = $interaction;
    }

    #[\Override]
    public function addInteractions(array $interactions): void
    {
        foreach ($interactions as $interaction) {
            $this->addInteraction($interaction);
        }
    }

    #[\Override]
    public function addInteractionBefore(IInteraction|Divider $interaction, string $beforeID): void
    {
        $position = -1;

        foreach ($this->getInteractions() as $key => $existingInteraction) {
            if ($existingInteraction instanceof IInteraction && $existingInteraction->getIdentifier() === $beforeID) {
                $position = $key;
                break;
            }
        }

        if ($position === -1) {
            throw new \InvalidArgumentException("Invalid interaction id '{$beforeID}' given.");
        }

        \array_splice($this->interactions, $position, 0, [
            $interaction,
        ]);
    }

    #[\Override]
    public function addInteractionAfter(IInteraction|Divider $interaction, string $afterID): void
    {
        $position = -1;

        foreach ($this->getInteractions() as $key => $existingInteraction) {
            if ($existingInteraction instanceof IInteraction && $existingInteraction->getIdentifier() === $afterID) {
                $position = $key;
                break;
            }
        }

        if ($position === -1) {
            throw new \InvalidArgumentException("Invalid interaction id '{$afterID}' given.");
        }

        \array_splice($this->interactions, $position + 1, 0, [
            $interaction,
        ]);
    }
}
