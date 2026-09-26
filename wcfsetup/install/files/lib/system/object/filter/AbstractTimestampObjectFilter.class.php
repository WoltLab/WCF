<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\WCF;

/**
 * Default implementation of a filter whose value is a point in time, stored as
 * a unix timestamp.
 *
 * The language item returned by `getLanguageItem()` is used as the title and
 * the label of the form field, the summary is read from the language item
 * `<languageItem>.summary` that receives the timestamp as `$value`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @implements IObjectFilter<TDatabaseObject, int>
 */
abstract class AbstractTimestampObjectFilter implements IObjectFilter
{
    /**
     * Returns the language item of the title of this filter.
     */
    abstract protected function getLanguageItem(): string;

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->getLanguageItem());
    }

    #[\Override]
    public function getFormField(): DateFormField
    {
        return DateFormField::create(\str_replace('.', '_', $this->getIdentifier()))
            ->label($this->getLanguageItem())
            ->supportTime()
            ->required();
    }

    /**
     * @param int|string $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)(int)$value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): int
    {
        return (int)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->getLanguageItem() . '.summary', [
            'value' => $value,
        ]);
    }
}
