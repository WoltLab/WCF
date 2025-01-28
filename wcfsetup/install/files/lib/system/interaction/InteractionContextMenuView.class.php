<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

class InteractionContextMenuView
{
    public function __construct(
        protected readonly IInteractionProvider $provider
    ) {}

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
        return WCF::getTPL()->fetch(
            'shared_interactionButton',
            'wcf',
            ['contextMenuOptions' => $this->renderContextMenuOptions($object)],
            true
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

    public function getInteractionsForObject(DatabaseObject $object): array
    {
        $interactions = \array_filter(
            $this->getInteractions(),
            fn(IInteraction|Divider $interaction) => $interaction instanceof Divider || $interaction->isAvailable($object)
        );

        return $this->removeObsoleteDividers($interactions);
    }

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
