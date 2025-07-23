<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\container\condition\PrefixConditionFormFieldContainer;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type Filter = array{condition: string, value: int}
 * @implements IDatabaseObjectListConditionType<UserList<User>, Filter>
 * @implements IObjectConditionType<User, Filter>
 * @extends AbstractConditionType<Filter>
 */
final class RegistrationDateUserConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType, IMigrateConditionType
{
    #[\Override]
    public function getFormField(string $id): PrefixConditionFormFieldContainer
    {
        return PrefixConditionFormFieldContainer::create($id)
            ->field(
                DateFormField::create("{$id}Value")
                    ->supportTime()
                    ->required()
            )
            ->prefixField(
                SingleSelectionFormField::create("{$id}Condition")
                    ->options(\array_combine($this->getConditions(), $this->getConditions()))
                    ->required()
            );
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'registrationDate';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.registrationDate';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        ["condition" => $condition, "value" => $time] = $this->filter;

        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.registrationDate {$condition} ?",
            [$time]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        ["condition" => $condition, "value" => $time] = $this->filter;

        return match ($condition) {
            ">" => $object->registrationDate > $time,
            "<" => $object->registrationDate < $time,
            ">=" => $object->registrationDate >= $time,
            "<=" => $object->registrationDate <= $time,
            default => false,
        };
    }

    #[\Override]
    public function getCategory(): string
    {
        return "user";
    }

    /**
     * @return string[]
     */
    private function getConditions(): array
    {
        return [">", "<", ">=", "<="];
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $registrationDateStart = $conditionData['registrationDateStart'] ?? null;
        $registrationDateEnd = $conditionData['registrationDateEnd'] ?? null;
        $conditions = [];

        if ($registrationDateStart !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => [
                    'value' => $this->convertDateStringTimestamp($registrationDateStart, 0, 0, 0),
                    'condition' => '>=',
                ],
            ];
        }
        if ($registrationDateEnd !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => [
                    'value' => $this->convertDateStringTimestamp($registrationDateEnd, 23, 59, 59),
                    'condition' => '<=',
                ],
            ];
        }

        unset($conditionData['registrationDateStart'], $conditionData['registrationDateEnd']);

        return $conditions;
    }

    private function convertDateStringTimestamp(string $date, int $hour, int $minute, int $seconds): int
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone(TIMEZONE));
        $dateTime = $dateTime->setTime($hour, $minute, $seconds);

        return $dateTime->getTimestamp();
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === "com.woltlab.wcf.user.registrationDate";
    }
}
