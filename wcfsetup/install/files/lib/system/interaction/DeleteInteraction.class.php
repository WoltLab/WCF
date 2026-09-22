<?php

namespace wcf\system\interaction;

use wcf\data\DatabaseObject;

/**
 * Represents a delete interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class DeleteInteraction extends RpcInteraction
{
    /**
     * @param list<string>|\Closure $affectedObjects Names of the objects that are implicitly deleted
     *                              along with the object itself. A closure receives the `DatabaseObject` the
     *                              interaction is rendered for and must return a list of names.
     */
    public function __construct(
        string $endpoint,
        ?\Closure $isAvailableCallback = null,
        private readonly array|\Closure $affectedObjects = []
    ) {
        parent::__construct(
            'delete',
            $endpoint,
            'wcf.global.button.delete',
            InteractionConfirmationType::Delete,
            '',
            $isAvailableCallback,
            InteractionEffect::RemoveItem
        );
    }

    #[\Override]
    protected function getAdditionalDataAttributes(DatabaseObject $object): array
    {
        if (\is_array($this->affectedObjects)) {
            $affectedObjects = $this->affectedObjects;
        } else {
            $affectedObjects = ($this->affectedObjects)($object);
        }

        if ($affectedObjects === []) {
            return [];
        }

        return [
            'data-affected-objects' => \json_encode(\array_values($affectedObjects), \JSON_THROW_ON_ERROR),
        ];
    }
}
