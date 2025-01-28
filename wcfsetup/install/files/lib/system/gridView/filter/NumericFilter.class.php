<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\NumericRangeFormField;

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
        string $databaseColumn = '',
        protected readonly bool $integerValues = false
    ) {
        parent::__construct($databaseColumn);
    }

    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return NumericRangeFormField::create($id)
            ->label($label)
            ->nullable()
            ->integerValues($this->integerValues);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list, $id);
        $values = $this->parseValue($value);

        if (!$values['from'] && !$values['to']) {
            return;
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
