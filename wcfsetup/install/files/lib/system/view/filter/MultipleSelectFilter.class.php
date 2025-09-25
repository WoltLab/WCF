<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\view\filter\exception\InvalidFilterValue;
use wcf\system\WCF;

/**
 * Allows a column to be filtered on the basis of multiple selected values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class MultipleSelectFilter extends AbstractFilter
{
    /**
     * @param array<string|int, mixed> $options
     */
    public function __construct(
        protected readonly array $options,
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        protected readonly bool $labelLanguageItems = true
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return MultipleSelectionFormField::create($this->id)
            ->label($this->languageItem)
            ->options($this->options, labelLanguageItems: $this->labelLanguageItems);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $filterValues = $this->unserializeValue($value);
        foreach ($filterValues as $filterValue) {
            if (!isset($this->options[$filterValue])) {
                throw new InvalidFilterValue("Invalid value '{$filterValue}' for filter '{$this->id}' given.");
            }
        }

        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} IN (?)", [$filterValues]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $renderedValues = \array_map(function ($filterValue): string {
            if ($this->labelLanguageItems) {
                return WCF::getLanguage()->get($this->options[$filterValue]);
            } else {
                return $this->options[$filterValue];
            }
        }, $this->unserializeValue($value));

        return \implode(', ', $renderedValues);
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', $value);
    }

    /**
     * @return array<string|int>
     */
    #[\Override]
    public function unserializeValue(string $value): array
    {
        return \explode(',', $value);
    }
}
