<?php

namespace wcf\system\interaction\bulk;

use wcf\system\interaction\InteractionConfirmationType;

/**
 * Represents a bulk delete interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkDeleteInteraction extends BulkRpcInteraction
{
    /**
     * @param list<string> $affectedObjects Names of the objects that are implicitly deleted
     *                     along with the objects themselves.
     */
    public function __construct(
        string $endpoint,
        ?\Closure $isAvailableCallback = null,
        private readonly array $affectedObjects = []
    ) {
        parent::__construct(
            'delete',
            $endpoint,
            'wcf.global.button.delete',
            InteractionConfirmationType::Delete,
            '',
            $isAvailableCallback
        );
    }

    #[\Override]
    protected function getAdditionalDataAttributes(array $objects): array
    {
        if ($this->affectedObjects === []) {
            return [];
        }

        return [
            'data-affected-objects' => \json_encode($this->affectedObjects, \JSON_THROW_ON_ERROR),
        ];
    }
}
