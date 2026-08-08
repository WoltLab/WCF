<?php

namespace wcf\system\form\option;

use wcf\data\DatabaseObjectList;
use wcf\system\database\table\column\AbstractDatabaseTableColumn;
use wcf\system\database\table\column\DateDatabaseTableColumn;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\form\option\formatter\DateFormatter;
use wcf\system\form\option\formatter\IFormOptionFormatter;

/**
 * Implementation of a form field for date values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class DateFormOption extends AbstractFormOption
{
    #[\Override]
    public function getId(): string
    {
        return 'date';
    }

    #[\Override]
    public function getFormField(string $id, array $configuration = []): AbstractFormField
    {
        $formField = DateFormField::create($id)
            ->saveValueFormat('Y-m-d')
            ->nullable();

        return $formField;
    }

    #[\Override]
    public function getFilterFormField(string $id, array $configuration = []): AbstractFormField
    {
        return DateRangeFormField::create($id)
            ->nullable();
    }

    #[\Override]
    public function getFormatter(): IFormOptionFormatter
    {
        return new DateFormatter();
    }

    #[\Override]
    public function getPlainTextFormatter(): IFormOptionFormatter
    {
        return $this->getFormatter();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $columnName, mixed $value): void
    {
        $values = $this->parseFilterValue($value);

        if ($values['from'] === 0 && $values['to'] === 0) {
            return;
        }

        if ($values['to'] === 0) {
            $list->getConditionBuilder()->add("{$columnName} >= ?", [$values['from']]);
        } else {
            $list->getConditionBuilder()->add("{$columnName} BETWEEN ? AND ?", [$values['from'], $values['to']]);
        }
    }

    #[\Override]
    public function renderFilterValue(string $value, array $configuration = []): string
    {
        $values = $this->parseFilterValue($value);

        if ($values['from'] !== 0 && $values['to'] !== 0) {
            return $values['from'] . ' ‐ ' . $values['to'];
        } else if ($values['from'] !== 0) {
            return '>= ' . $values['from'];
        } else if ($values['to'] !== 0) {
            return '<= ' . $values['to'];
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

        $values = \explode(';', $value);
        if (\count($values) === 2) {
            $from = (int)$values[0];
            $to = (int)$values[1];
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return DateDatabaseTableColumn::create($name);
    }
}
