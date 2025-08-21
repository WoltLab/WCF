<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\NumericRangeFormField;

/**
 * Implementation of a form field for currency values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractNumericFormOption extends AbstractFormOption
{
    #[\Override]
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField
    {
        return NumericRangeFormField::create($id)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void
    {
        $values = $this->parseFilterValue($value);

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
    public function renderFilterValue(string $value, array $configuration = []): string
    {
        $values = $this->parseFilterValue($value);
        $suffix = $this->getSuffix($configuration);
        if ($suffix !== '') {
            $suffix = ' ' . $suffix;
        }

        if ($values['from'] && $values['to']) {
            return $values['from'] . ' ‐ ' . $values['to'] . $suffix;
        } else if ($values['from']) {
            return '>= ' . $values['from'] . $suffix;
        } else if ($values['to']) {
            return '<= ' . $values['to'] . $suffix;
        }

        return '';
    }

    /**
     * @return array{from: int, to: int}
     */
    protected function parseFilterValue(string $value): array
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

    /**
     * @param array<string, mixed> $configuration
     */
    protected function getSuffix(array $configuration): string
    {
        return '';
    }
}
