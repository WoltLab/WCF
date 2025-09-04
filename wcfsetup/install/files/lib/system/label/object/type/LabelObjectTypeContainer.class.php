<?php

namespace wcf\system\label\object\type;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

/**
 * This type of container is used to allow the user when editing label groups to specify
 * in which sub-areas (e.g., categories or sub-forums) a label group is available.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @implements \Iterator<int, LabelObjectType>
 */
final class LabelObjectTypeContainer implements \Countable, \Iterator
{
    /**
     * @var LabelObjectType[]
     */
    private array $objectTypes = [];

    private int $position = 0;

    public function __construct(
        public readonly int $objectTypeID,
        public readonly string $title = ''
    ) {}

    /**
     * Adds a label object type.
     */
    public function add(LabelObjectType $objectType): void
    {
        $this->objectTypes[] = $objectType;
    }

    public function getObjectTypeName(): string
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID)->objectType;
    }

    public function getTitle(): string
    {
        return $this->title ?: WCF::getLanguage()->get('wcf.acp.label.container.' . $this->getObjectTypeName());
    }

    #[\Override]
    public function current(): LabelObjectType
    {
        return $this->objectTypes[$this->position];
    }

    #[\Override]
    public function key(): int
    {
        return $this->position;
    }

    #[\Override]
    public function next(): void
    {
        $this->position++;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\Override]
    public function valid(): bool
    {
        return isset($this->objectTypes[$this->position]);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->objectTypes);
    }
}
