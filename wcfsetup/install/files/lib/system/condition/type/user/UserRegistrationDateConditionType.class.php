<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\AbstractConditionFormField;
use wcf\system\form\builder\field\DateConditionFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 * @extends AbstractConditionType<string>
 */
final class UserRegistrationDateConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): AbstractConditionFormField
    {
        return DateConditionFormField::create($id)
            ->conditions(\array_combine($this->getConditions(), $this->getConditions()))
            ->nullable()
            ->supportTime();
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
        ["condition" => $condition, "time" => $time] = $this->getParsedFilter();

        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.registrationDate {$condition} ?",
            [$time]
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        ["condition" => $condition, "time" => $time] = $this->getParsedFilter();

        return match ($condition) {
            ">" => $object->registrationDate > $time,
            "<" => $object->registrationDate < $time,
            ">=" => $object->registrationDate >= $time,
            "<=" => $object->registrationDate <= $time,
            default => false,
        };
    }

    /**
     * @return array{condition: string, time: int}
     */
    private function getParsedFilter(): array
    {
        ["condition" => $condition, "value" => $filter] = @\unserialize($this->filter);
        $dateTime = \DateTime::createFromFormat(
            DateConditionFormField::TIME_FORMAT,
            $filter,
            new \DateTimeZone(TIMEZONE),
        );

        return [
            'condition' => $condition,
            'time' => $dateTime->getTimestamp(),
        ];
    }

    /**
     * @return string[]
     */
    private function getConditions(): array
    {
        return [">", "<", ">=", "<="];
    }
}
