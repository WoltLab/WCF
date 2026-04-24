<?php

namespace wcf\data;

use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Trait for dbo collections with message embedded objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
trait TCollectionEmbeddedObjects
{
    private bool $embeddedObjectsLoaded = false;

    public function loadEmbeddedObjects(string $messageObjectType): void
    {
        if ($this->embeddedObjectsLoaded) {
            return;
        }

        $this->embeddedObjectsLoaded = true;

        $objectIDs = $this->getEmbeddedObjectIDs();
        if ($objectIDs === []) {
            return;
        }

        MessageEmbeddedObjectManager::getInstance()->loadObjects(
            $messageObjectType,
            $objectIDs,
            $this->getContentLanguageID(),
        );
    }

    protected function getContentLanguageID(): ?int
    {
        return null;
    }

    protected function hasEmbeddedObjects(DatabaseObject $object): bool
    {
        // @phpstan-ignore property.notFound
        return $object->hasEmbeddedObjects === 1;
    }

    /**
     * @return int[]
     */
    private function getEmbeddedObjectIDs(): array
    {
        \assert($this instanceof DatabaseObjectCollection);

        return \array_map(
            static fn(DatabaseObject $object) => $object->getObjectID(),
            \array_filter($this->getObjects(), fn(DatabaseObject $object) => $this->hasEmbeddedObjects($object))
        );
    }
}
