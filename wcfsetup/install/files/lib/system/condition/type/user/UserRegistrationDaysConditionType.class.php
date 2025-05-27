<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\IntegerConditionFormField;
use wcf\util\DateUtil;

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
        ["condition" => $condition, "timestamp" => $timestamp] = $this->getParsedFilter();

        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.registrationDate {$condition} ?",
            [$timestamp]
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        ["condition" => $condition, "timestamp" => $timestamp] = $this->getParsedFilter();

        return match ($condition) {
            '>' => $object->registrationDate < $timestamp,
            '<' => $object->registrationDate > $timestamp,
            '>=' => $object->registrationDate <= $timestamp,
            '<=' => $object->registrationDate >= $timestamp,
            default => throw new \InvalidArgumentException("Unknown condition: {$condition}"),
        };
    }

    /**
     * @return array{condition: string, timestamp: int}
     */
    private function getParsedFilter(): array
    {
        if (!isset($this->filter['condition'], $this->filter['value'])) {
            throw new \InvalidArgumentException("Invalid filter format");
        }

        $date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
        $date->setTimezone(new \DateTimeZone(TIMEZONE));
        $date->sub(new \DateInterval("P{$this->filter['condition']}D"));

        return [
            'condition' => $this->filter['condition'],
            'timestamp' => $date->getTimestamp(),
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
