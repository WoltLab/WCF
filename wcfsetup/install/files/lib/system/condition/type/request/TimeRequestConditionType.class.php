<?php

namespace wcf\system\condition\type\request;

use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IGlobalConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\form\builder\container\condition\RowConditionFormFieldContainer;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TimeFormField;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type Filter = array{Condition: string, Value: string, Timezone: string}
 * @implements IGlobalConditionType<Filter>
 * @extends AbstractConditionType<Filter>
 */
final class TimeRequestConditionType extends AbstractConditionType implements IGlobalConditionType, IMigrateConditionType
{
    public const USER_TIMEZONE = 'userTimezone';

    #[\Override]
    public function getIdentifier(): string
    {
        return 'time';
    }

    #[\Override]
    public function getLabel(): string
    {
        return "wcf.condition.request.time";
    }

    #[\Override]
    public function getFormField(string $id): RowConditionFormFieldContainer
    {
        return RowConditionFormFieldContainer::create($id)
            ->appendChildren([
                SingleSelectionFormField::create("{$id}Condition")
                    ->options(\array_combine($this->getConditions(), $this->getConditions()))
                    ->required(),
                TimeFormField::create("{$id}Value")
                    ->required(),
                SingleSelectionFormField::create("{$id}Timezone")
                    ->options($this->getTimezones())
                    ->required(),
            ]);
    }

    #[\Override]
    public function matches(): bool
    {
        ["Condition" => $condition, "Value" => $time, "Timezone" => $timezone] = $this->filter;
        if ($timezone === self::USER_TIMEZONE) {
            $timezoneObject = WCF::getUser()->getTimezone();
        } else {
            $timezoneObject = new \DateTimeZone($timezone);
        }

        $dateTime = \DateTimeImmutable::createFromFormat('H:i', $time, $timezoneObject);

        return match ($condition) {
            '>' => TIME_NOW > $dateTime->getTimestamp(),
            '<' => TIME_NOW < $dateTime->getTimestamp(),
            '>=' => TIME_NOW >= $dateTime->getTimestamp(),
            '<=' => TIME_NOW <= $dateTime->getTimestamp(),
            default => false,
        };
    }

    /**
     * @return array<string, string>
     */
    public function getTimezones(): array
    {
        return \array_merge(
            [self::USER_TIMEZONE => 'wcf.date.timezone.user'],
            \array_combine(
                DateUtil::getAvailableTimezones(),
                \array_map(
                    static fn (string $timezone): string => WCF::getLanguage()->get(
                        'wcf.date.timezone.' . \str_replace(
                            '/',
                            '.',
                            \strtolower($timezone)
                        )
                    ),
                    DateUtil::getAvailableTimezones()
                )
            )
        );
    }

    /**
     * @return string[]
     */
    protected function getConditions(): array
    {
        return [">", "<", ">=", "<="];
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $startTime = $conditionData['startTime'] ?? null;
        $endTime = $conditionData['endTime'] ?? null;
        $timezone = $conditionData['timezone'] ?? self::USER_TIMEZONE;
        $conditions = [];

        if ($startTime !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => ["Value" => $startTime, 'Condition' => '>', 'Timezone' => $timezone],
            ];
        }
        if ($endTime !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => ["Value" => $endTime, 'Condition' => '<', 'Timezone' => $timezone],
            ];
        }

        unset($conditionData['startTime'], $conditionData['endTime'], $conditionData['timezone']);

        return $conditions;
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.pointInTime.time';
    }
}
