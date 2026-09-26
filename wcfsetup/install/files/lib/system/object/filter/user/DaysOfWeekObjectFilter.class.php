<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\object\filter\AbstractMultipleSelectionObjectFilter;
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
 * @extends AbstractMultipleSelectionObjectFilter<User>
 */
final class DaysOfWeekObjectFilter extends AbstractMultipleSelectionObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.daysOfWeek';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.daysOfWeek';
    }

    #[\Override]
    protected function getLabels(array $objectIDs): array
    {
        return \array_values(\array_map(
            static fn(string $day) => WCF::getLanguage()->get($day),
            \array_intersect_key($this->getDays(), \array_flip($objectIDs)),
        ));
    }

    #[\Override]
    public function getFormField(): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create('daysOfWeek')
            ->label('wcf.date.daysOfWeek')
            ->options($this->getDays())
            ->required();
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
