<?php

namespace wcf\data;

use wcf\data\label\Label;
use wcf\system\label\object\ILabelObjectHandler;

/**
 * Trait for dbo collections with labels.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TCollectionLabels
{
    /**
     * @var array<int, Label[]>
     */
    private array $labels;

    /**
     * @return Label[]
     */
    public function getLabels(DatabaseObject $object): array
    {
        $this->loadLabels();

        return $this->labels[$object->getObjectID()] ?? [];
    }

    private function loadLabels(): void
    {
        if (isset($this->labels)) {
            return;
        }

        $this->labels = [];

        $objectIDs = $this->getLabeledObjectIDs();
        if ($objectIDs === []) {
            return;
        }

        $this->labels = $this->getLabelObjectHandler()->getAssignedLabels($objectIDs);
    }

    /**
     * @return int[]
     */
    private function getLabeledObjectIDs(): array
    {
        \assert($this instanceof DatabaseObjectCollection);

        return \array_map(
            fn(DatabaseObject $object) => $object->getObjectID(),
            \array_filter($this->getObjects(), fn(DatabaseObject $object) => $this->hasLabels($object))
        );
    }

    protected function hasLabels(DatabaseObject $object): bool
    {
        // @phpstan-ignore property.notFound
        return $object->hasLabels === 1;
    }

    protected abstract function getLabelObjectHandler(): ILabelObjectHandler;
}
