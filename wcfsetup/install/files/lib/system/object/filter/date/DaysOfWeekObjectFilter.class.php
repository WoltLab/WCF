<?php

namespace wcf\system\object\filter\date;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Filters by whether the current day of the week is one of the given days,
 * based on the time zone of the user. The days are numbered from `0` (Sunday)
 * to `6` (Saturday).
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectFilter<User, list<int>>
 */
final class DaysOfWeekObjectFilter implements IObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.daysOfWeek';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.daysOfWeek');
    }

    #[\Override]
    public function getFormField(): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create('daysOfWeek')
            ->label('wcf.date.daysOfWeek')
            ->options($this->getDays())
            ->required();
    }

    /**
     * @param list<int|string> $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', \array_map(static fn($day) => (int)$day, $value));
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        return \array_map(static fn($day) => (int)$day, \explode(',', $serializedValue));
    }

    /**
     * @return list<int>
     */
    #[\Override]
    public function toFormFieldValue(mixed $value): array
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        $days = \array_intersect_key($this->getDays(), \array_flip($value));

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.daysOfWeek.summary', [
            'days' => \implode(', ', \array_map(
                static fn(string $day) => WCF::getLanguage()->get($day),
                $days,
            )),
        ]);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        $now = new \DateTimeImmutable('now', $object->getTimeZone());

        return \in_array((int)$now->format('w'), $configuredValue, true);
    }

    /**
     * Returns the language items of the days of the week, indexed by their
     * number and sorted by the first day of the week.
     *
     * @return array<int, string>
     */
    private function getDays(): array
    {
        return \array_map(
            static fn(string $day) => 'wcf.date.day.' . $day,
            DateUtil::getWeekDays(),
        );
    }
}
