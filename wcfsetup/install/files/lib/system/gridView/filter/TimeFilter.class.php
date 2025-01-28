<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\WCF;

/**
 * Filter for columns that contain unix timestamps.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TimeFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return DateRangeFormField::create($id)
            ->label($label)
            ->nullable()
            ->supportTime();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list, $id);
        $timestamps = $this->getTimestamps($value);

        if (!$timestamps['from'] && !$timestamps['to']) {
            return;
        }

        if (!$timestamps['to']) {
            $list->getConditionBuilder()->add("{$columnName} >= ?", [$timestamps['from']]);
        } else {
            $list->getConditionBuilder()->add("{$columnName} BETWEEN ? AND ?", [$timestamps['from'], $timestamps['to']]);
        }
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $values = explode(';', $value);
        if (\count($values) !== 2) {
            return '';
        }

        $locale = WCF::getLanguage()->getLocale();;
        $fromString = $toString = '';
        if ($values[0] !== '') {
            $fromDateTime = \DateTime::createFromFormat(
                'Y-m-d\TH:i:sP',
                $values[0],
                WCF::getUser()->getTimeZone()
            );
            if ($fromDateTime !== false) {
                $fromString = \IntlDateFormatter::formatObject(
                    $fromDateTime,
                    [
                        \IntlDateFormatter::LONG,
                        \IntlDateFormatter::SHORT,
                    ],
                    $locale
                );
            }
        }
        if ($values[1] !== '') {
            $toDateTime = \DateTime::createFromFormat(
                'Y-m-d\TH:i:sP',
                $values[1],
                WCF::getUser()->getTimeZone()
            );
            if ($toDateTime !== false) {
                $toString = \IntlDateFormatter::formatObject(
                    $toDateTime,
                    [
                        \IntlDateFormatter::LONG,
                        \IntlDateFormatter::SHORT,
                    ],
                    $locale
                );
            }
        }

        if ($fromString && $toString) {
            return $fromString . ' ‐ ' . $toString;
        } else if ($fromString) {
            return '>= ' . $fromString;
        } else if ($toString) {
            return '<= ' . $toString;
        }

        return '';
    }

    private function getTimestamps(string $value): array
    {
        $from = 0;
        $to = 0;

        $values = explode(';', $value);
        if (\count($values) === 2) {
            $from = $this->getTimestamp($values[0]);
            $to = $this->getTimestamp($values[1]);
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    private function getTimestamp(string $date): int
    {
        $dateTime = \DateTime::createFromFormat(
            'Y-m-d\TH:i:sP',
            $date
        );

        if ($dateTime !== false) {
            return $dateTime->getTimestamp();
        }

        return 0;
    }
}
