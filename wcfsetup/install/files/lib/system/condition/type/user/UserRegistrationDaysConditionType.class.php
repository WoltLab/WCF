<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\IntegerConditionFormField;

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
final class UserRegistrationDaysConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): IntegerConditionFormField
    {
        return IntegerConditionFormField::create($id)
            ->conditions($this->getConditions())
            ->suffix("wcf.acp.option.suffix.days");
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'registrationDays';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.registrationDays';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        ["condition" => $condition, "value" => $days] = @\unserialize($this->filter);

        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.registrationDate {$condition} ?",
            [TIME_NOW - $days * 86_400]
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        ["condition" => $condition, "value" => $days] = @\unserialize($this->filter);

        return match ($condition) {
            '>' => $object->registrationDate < TIME_NOW - $days * 86_400,
            '<' => $object->registrationDate > TIME_NOW - $days * 86_400,
            '>=' => $object->registrationDate <= TIME_NOW - $days * 86_400,
            '<=' => $object->registrationDate >= TIME_NOW - $days * 86_400,
            default => throw new \InvalidArgumentException("Unknown condition: {$condition}"),
        };
    }

    /**
     * @return array{condition: string, timestamp: int}
     */
    private function getParsedFilter(): array
    {
        $filter = @\unserialize($this->filter);
        if (!\is_array($filter) || !isset($filter['condition'], $filter['value'])) {
            throw new \InvalidArgumentException("Invalid filter format");
        }

        return [
            'condition' => $filter['condition'],
            'timestamp' => TIME_NOW - ($filter['value'] * 86_400),
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
