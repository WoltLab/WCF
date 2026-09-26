<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\WCF;

/**
 * Default implementation of a filter whose value is a yes/no choice.
 *
 * The language item returned by `getLanguageItem()` is used as the title and
 * the label of the form field, the summaries are read from the language items
 * `<languageItem>.summary.yes` and `<languageItem>.summary.no`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @implements IObjectFilter<TDatabaseObject, bool>
 */
abstract class AbstractBooleanObjectFilter implements IObjectFilter
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
    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create(\str_replace('.', '_', $this->getIdentifier()))
            ->label($this->getLanguageItem());
    }

    /**
     * @param bool|int $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): bool
    {
        return (bool)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): bool
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->get(
            $this->getLanguageItem() . ($value ? '.summary.yes' : '.summary.no')
        );
    }
}
