<?php

namespace wcf\system\interaction\bulk;

use wcf\data\DatabaseObject;
use wcf\system\interaction\Divider;
use wcf\system\WCF;

/**
 * Represents the component of an bulk interaction content menu.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkInteractionContextMenuComponent
{
    public function __construct(
        protected readonly IBulkInteractionProvider $provider
    ) {}

    /**
     * @param DatabaseObject[] $objects
     */
    public function renderContextMenuOptions(array $objects): string
    {
        $html = '';

        $interactions = $this->getInteractionsForObjects($objects);

        foreach ($interactions as $interaction) {
            if ($interaction instanceof Divider) {
                $html .= '<li class="dropdownDivider"></li>';
            } else {
                $availableForObjects = \array_filter(
                    $objects,
                    static fn(DatabaseObject $object) => $interaction->isAvailable($object)
                );
                $option = $interaction->render($availableForObjects);
                if ($option === '') {
                    continue;
                }

                $html .= '<li>' . $option . '</li>';
            }
        }

        if ($html === '') {
            $html = '<li class="disabled"><span>'
                . WCF::getLanguage()->get('wcf.clipboard.button.noInteractionsAvailable')
                . '</span></li>';
        }

        return $html;
    }

    /**
     * @param DatabaseObject[] $objects
     * @return (IBulkInteraction|Divider)[]
     */
    public function getInteractionsForObjects(array $objects): array
    {
        $interactions = [];

        foreach ($this->provider->getInteractions() as $interaction) {
            if ($interaction instanceof Divider) {
                $interactions[] = $interaction;
                continue;
            }

            $availableForObjects = \array_filter(
                $objects,
                static fn(DatabaseObject $object) => $interaction->isAvailable($object)
            );
            if ($availableForObjects === []) {
                continue;
            }

            $interactions[] = $interaction;
        }

        return $this->removeObsoleteDividers($interactions);
    }

    /**
     * @param (IBulkInteraction|Divider)[] $interactions
     * @return (IBulkInteraction|Divider)[]
     */
    private function removeObsoleteDividers(array $interactions): array
    {
        $previousElementIsDivider = true;
        $interactions = \array_filter(
            $interactions,
            static function (IBulkInteraction|Divider $interaction) use (&$previousElementIsDivider) {
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
