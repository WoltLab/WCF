<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\view\filter\exception\InvalidFilterValue;

/**
 * Filter for columns that contain numerics.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class NumericFilter extends AbstractFilter
{
    public function __construct(
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        protected readonly bool $integerValues = false,
        protected readonly null|int|float $minimum = null,
        protected readonly null|int|float $maximum = null,
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return NumericRangeFormField::create($this->id)
            ->label($this->languageItem)
            ->nullable()
            ->integerValues($this->integerValues)
            ->minimum($this->minimum)
            ->maximum($this->maximum);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);
        $values = $this->parseValue($value);

        if (!$values['from'] && !$values['to']) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$this->id}' given.");
        }

        if (!$values['to']) {
            $list->getConditionBuilder()->add("{$columnName} >= ?", [$values['from']]);
        } else {
            $list->getConditionBuilder()->add("{$columnName} BETWEEN ? AND ?", [$values['from'], $values['to']]);
        }
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $values = $this->parseValue($value);

        if ($values['from'] && $values['to']) {
            return $values['from'] . ' ‐ ' . $values['to'];
        } else if ($values['from']) {
            return '>= ' . $values['from'];
        } else if ($values['to']) {
            return '<= ' . $values['to'];
        }

        return '';
    }

    /**
     * @return array{from: int, to: int}
     */
    private function parseValue(string $value): array
    {
        $from = 0;
        $to = 0;

        $values = explode(';', $value);
        if (\count($values) === 2) {
            $from = $values[0];
            $to = $values[1];
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }
}
