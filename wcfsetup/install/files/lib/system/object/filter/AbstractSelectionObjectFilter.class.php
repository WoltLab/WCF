<?php

namespace wcf\system\object\filter;

use wcf\data\DatabaseObject;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\WCF;

/**
 * Default implementation of a filter whose value is a single object selected
 * from a list, e.g. a user group. The value is the id of the selected object.
 *
 * The language item returned by `getLanguageItem()` is used as the title and
 * the label of the form field, the summary is read from the language item
 * `<languageItem>.summary` that receives the label of the selected object as
 * `$value`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject
 * @implements IObjectFilter<TDatabaseObject, int>
 */
abstract class AbstractSelectionObjectFilter implements IObjectFilter
{
    /**
     * @var array<int, string>
     */
    private array $options;

    /**
     * Returns the language item of the title of this filter.
     */
    abstract protected function getLanguageItem(): string;

    /**
     * Returns the labels of the selectable objects, indexed by their id and
     * sorted in the order they are offered.
     *
     * @return array<int, string>
     */
    abstract protected function createOptions(): array;

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->getLanguageItem());
    }

    #[\Override]
    public function getFormField(): SelectFormField
    {
        return SelectFormField::create(\str_replace('.', '_', $this->getIdentifier()))
            ->label($this->getLanguageItem())
            ->options($this->getOptions(), labelLanguageItems: false)
            ->required();
    }

    /**
     * @param int|string $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): int
    {
        return (int)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): int
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->getLanguageItem() . '.summary', [
            'value' => $this->getOptions()[$value] ?? $value,
        ]);
    }

    /**
     * Returns the labels of the selectable objects, indexed by their id.
     *
     * @return array<int, string>
     */
    protected function getOptions(): array
    {
        if (!isset($this->options)) {
            $this->options = $this->createOptions();
        }

        return $this->options;
    }
}
