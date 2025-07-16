<?php

namespace wcf\system\condition\type\request;

use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IGlobalConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IGlobalConditionType<string>
 * @extends AbstractConditionType<string>
 */
final class NotDayOfWeekRequestConditionType extends AbstractConditionType implements IGlobalConditionType, IMigrateConditionType
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'notDayOfWeek';
    }

    #[\Override]
    public function getLabel(): string
    {
        return "wcf.condition.request.notDayOfWeek";
    }

    #[\Override]
    public function getFormField(string $id): SingleSelectionFormField
    {
        return SingleSelectionFormField::create($id)
            ->options(
                \array_map(
                    static fn ($day) => WCF::getLanguage()->get('wcf.date.day.' . $day),
                    DateUtil::getWeekDays()
                )
            )
            ->required();
    }

    #[\Override]
    public function matches(): bool
    {
        $dateTime = new \DateTimeImmutable("@" . TIME_NOW, WCF::getUser()->getTimeZone());

        return $dateTime->format('w') !== $this->filter;
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $daysOfWeeks = $conditionData['daysOfWeek'] ?? [];
        if (\count($daysOfWeeks) <= 1) {
            // `DayOfWeekRequestConditionType` should migrate the data.
            return [];
        }

        if (\count($daysOfWeeks) === 7) {
            // If all days have been selected, this condition is unnecessary and can be removed.
            unset($conditionData['daysOfWeek']);

            return [];
        }

        $conditions = [];

        // We must remove all selected week of days to convert the previous condition from an “or” to an “and” condition.
        $daysOfWeeks = \array_diff_key(DateUtil::getWeekDays(), $daysOfWeeks);

        foreach ($daysOfWeeks as $dayOfWeek => $_) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => (string)$dayOfWeek,
            ];
        }

        unset($conditionData['daysOfWeek']);

        return $conditions;
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.pointInTime.daysOfWeek';
    }
}
