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
use wcf\system\WCF;

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

        if ($values['from'] === '' && $values['to'] === '') {
            return;
        }

        // Dates are stored as `Y-m-d`, which sorts chronologically. Empty values
        // are stored as `0000-00-00` and must not match an upper bound.
        $list->getConditionBuilder()->add("{$columnName} > ?", ['0000-00-00']);

        if ($values['from'] !== '') {
            $list->getConditionBuilder()->add("{$columnName} >= ?", [$values['from']]);
        }
        if ($values['to'] !== '') {
            $list->getConditionBuilder()->add("{$columnName} <= ?", [$values['to']]);
        }
    }

    #[\Override]
    public function renderFilterValue(string $value, array $configuration = []): string
    {
        $values = $this->parseFilterValue($value);
        $from = $values['from'] !== '' ? $this->formatFilterDate($values['from'], $configuration) : '';
        $to = $values['to'] !== '' ? $this->formatFilterDate($values['to'], $configuration) : '';

        if ($from !== '' && $to !== '') {
            return $from . ' ‐ ' . $to;
        } else if ($from !== '') {
            return '>= ' . $from;
        } else if ($to !== '') {
            return '<= ' . $to;
        }

        return '';
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function formatFilterDate(string $date, array $configuration): string
    {
        return $this->getPlainTextFormatter()->format($date, WCF::getLanguage()->languageID, $configuration);
    }

    /**
     * Returns the bounds of the given filter value as `Y-m-d`, invalid or
     * missing bounds are returned as an empty string.
     *
     * @return array{from: string, to: string}
     */
    protected function parseFilterValue(string $value): array
    {
        $values = \explode(';', $value);
        if (\count($values) !== 2) {
            return [
                'from' => '',
                'to' => '',
            ];
        }

        return [
            'from' => $this->parseFilterDate($values[0]),
            'to' => $this->parseFilterDate($values[1]),
        ];
    }

    private function parseFilterDate(string $date): string
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($dateTime === false || $dateTime->format('Y-m-d') !== $date) {
            return '';
        }

        return $date;
    }

    #[\Override]
    public function getDatabaseTableColumn(string $name): AbstractDatabaseTableColumn
    {
        return DateDatabaseTableColumn::create($name);
    }

    #[\Override]
    public function matchesFilterValue(string $optionValue, string $filterValue, array $configuration = []): bool
    {
        $values = $this->parseFilterValue($filterValue);

        if ($values['from'] === '' && $values['to'] === '') {
            return true;
        }

        if ($optionValue === '' || $optionValue === '0000-00-00') {
            return false;
        }

        if ($values['from'] !== '' && $optionValue < $values['from']) {
            return false;
        }

        return $values['to'] === '' || $optionValue <= $values['to'];
    }
}
