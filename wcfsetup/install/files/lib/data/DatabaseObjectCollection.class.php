<?php

namespace wcf\data;

/**
 * Abstract class for a collection of database objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TDatabaseObject of DatabaseObject
 */
abstract class DatabaseObjectCollection
{
    /**
     * @param TDatabaseObject[] $objects
     */
    public function __construct(protected readonly array $objects)
    {
        if ($objects === []) {
            throw new \InvalidArgumentException("A collection must contain at least one object.");
        }
    }

    /**
     * @return non-empty-array<int>
     */
    public function getObjectIDs(): array
    {
        return \array_map(static fn($object) => $object->getObjectID(), $this->objects);
    }

    /**
     * @return non-empty-array<TDatabaseObject>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
