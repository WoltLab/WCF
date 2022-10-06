<?php

namespace wcf\system\label\object\type;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Label object type container.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Label\Object\Type
 */
final class LabelObjectTypeContainer implements \Countable, \Iterator
{
    /**
     * list of object types
     * @var LabelObjectType[]
     */
    public array $objectTypes = [];

    /**
     * object type id
     */
    public int $objectTypeID = 0;

    /**
     * iterator position
     */
    private int $position = 0;

    /**
     * Creates a new LabelObjectTypeContainer object.
     */
    public function __construct(int $objectTypeID)
    {
        $this->objectTypeID = $objectTypeID;
    }

    /**
     * Adds a label object type.
     */
    public function add(LabelObjectType $objectType): void
    {
        $this->objectTypes[] = $objectType;
    }

    /**
     * Returns the object type id.
     */
    public function getObjectTypeID(): int
    {
        return $this->objectTypeID;
    }

    /**
     * Returns the object type name.
     */
    public function getObjectTypeName(): string
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->getObjectTypeID())->objectType;
    }

    /**
     * @inheritDoc
     */
    public function current(): LabelObjectType
    {
        return $this->objectTypes[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->objectTypes[$this->position]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->objectTypes);
    }
}
