<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Default implementation of a filter whose value is a list of object ids, the
 * tested object must match one of them.
 *
 * The language item returned by `getLanguageItem()` is used as the title, the
 * summary is read from the language item `<languageItem>.summary` that receives
 * the comma-separated labels of the selected objects as `$values`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @implements IObjectFilter<TDatabaseObject, list<int>>
 */
abstract class AbstractMultipleSelectionObjectFilter implements IObjectFilter
{
    /**
     * Returns the language item of the title of this filter.
     */
    abstract protected function getLanguageItem(): string;

    /**
     * Returns the labels of the objects with the given ids, ids of unknown
     * objects are skipped.
     *
     * @param list<int> $objectIDs
     * @return list<string>
     */
    abstract protected function getLabels(array $objectIDs): array;

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->getLanguageItem());
    }

    /**
     * @param list<int|string> $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', \array_map(static fn($objectID) => (int)$objectID, $value));
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        return \array_map(static fn($objectID) => (int)$objectID, \explode(',', $serializedValue));
    }

    /**
     * @return list<int>
     */
    #[\Override]
    public function toFormFieldValue(mixed $value): array
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->getLanguageItem() . '.summary', [
            'values' => \implode(', ', $this->getLabels($value)),
        ]);
    }
}
