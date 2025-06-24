<?php

namespace wcf\system\listView\filter;

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
class DateFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return DateRangeFormField::create($this->id)
            ->label($this->languageItem)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);
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

        $locale = WCF::getLanguage()->getLocale();
        $fromString = $toString = '';
        if ($values[0] !== '') {
            $fromDateTime = \DateTime::createFromFormat(
                'Y-m-d',
                $values[0],
                WCF::getUser()->getTimeZone()
            );
            if ($fromDateTime !== false) {
                $fromString = \IntlDateFormatter::formatObject(
                    $fromDateTime,
                    [
                        \IntlDateFormatter::LONG,
                        \IntlDateFormatter::NONE,
                    ],
                    $locale
                );
            }
        }
        if ($values[1] !== '') {
            $toDateTime = \DateTime::createFromFormat(
                'Y-m-d',
                $values[1],
                WCF::getUser()->getTimeZone()
            );
            if ($toDateTime !== false) {
                $toString = \IntlDateFormatter::formatObject(
                    $toDateTime,
                    [
                        \IntlDateFormatter::LONG,
                        \IntlDateFormatter::NONE,
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

    /**
     * @return array{from: int, to: int}
     */
    private function getTimestamps(string $value): array
    {
        $from = 0;
        $to = 0;

        $values = explode(';', $value);
        if (\count($values) === 2) {
            $fromDateTime = \DateTime::createFromFormat(
                'Y-m-d',
                $values[0]
            );
            if ($fromDateTime !== false) {
                $fromDateTime->setTime(0, 0);
                $from = $fromDateTime->getTimestamp();
            }

            $toDateTime = \DateTime::createFromFormat(
                'Y-m-d',
                $values[1]
            );
            if ($toDateTime !== false) {
                $toDateTime->setTime(23, 59, 59);
                $to = $toDateTime->getTimestamp();
            }
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }
}
