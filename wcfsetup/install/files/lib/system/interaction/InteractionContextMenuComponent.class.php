<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents the component of an interaction content menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class InteractionContextMenuComponent
{
    public function __construct(
        protected readonly IInteractionProvider $provider,
        protected ?InteractionContextMenuComponentConfiguration $configuration = null
    ) {
        if ($this->configuration === null) {
            $this->configuration = InteractionContextMenuComponentConfiguration::forDefault();
        }
    }

    public function renderContextMenuOptions(DatabaseObject $object): string
    {
        $html = '';

        $interactions = $this->getInteractionsForObject($object);

        foreach ($interactions as $interaction) {
            if ($interaction instanceof Divider) {
                $html .= '<li class="dropdownDivider"></li>';
            } else {
                $html .= '<li>' . $interaction->render($object) . '</li>';
            }
        }

        return $html;
    }

    public function renderButton(DatabaseObject $object): string
    {
        $options = $this->renderContextMenuOptions($object);
        if (!$options) {
            return '';
        }

        return WCF::getTPL()->render(
            'wcf',
            'shared_interactionButton',
            [
                'contextMenuOptions' => $this->renderContextMenuOptions($object),
                'configuration' => $this->configuration,
            ]
        );
    }

    /**
     * Renders the initialization code for the interactions.
     */
    public function renderInitialization(string $containerId): string
    {
        return implode(
            "\n",
            \array_map(
                fn($interaction) => $interaction->renderInitialization($containerId),
                \array_filter(
                    $this->getInteractions(),
                    fn(IInteraction|Divider $interaction) => $interaction instanceof IInteraction
                )
            )
        );
    }

    /**
     * @return (IInteraction|Divider)[]
     */
    public function getInteractionsForObject(DatabaseObject $object): array
    {
        $interactions = \array_filter(
            $this->getInteractions(),
            fn(IInteraction|Divider $interaction) => $interaction instanceof Divider || $interaction->isAvailable($object)
        );

        return $this->removeObsoleteDividers($interactions);
    }

    /**
     * @return (IInteraction|Divider)[]
     */
    public function getInteractions(): array
    {
        return $this->provider->getInteractions();
    }

    /**
     * @param (IInteraction|Divider)[] $interactions
     * @return (IInteraction|Divider)[]
     */
    private function removeObsoleteDividers(array $interactions): array
    {
        $previousElementIsDivider = true;
        $interactions = \array_filter(
            $interactions,
            static function (IInteraction|Divider $interaction) use (&$previousElementIsDivider) {
                if ($interaction instanceof Divider) {
                    if ($previousElementIsDivider) {
                        return false;
                    }

                    $previousElementIsDivider = true;
                } else {
                    $previousElementIsDivider = false;
                }

                return true;
            }
        );

        $lastKey = \array_key_last($interactions);
        if ($lastKey !== null && $interactions[$lastKey] instanceof Divider) {
            \array_pop($interactions);
        }

        return $interactions;
    }
}
