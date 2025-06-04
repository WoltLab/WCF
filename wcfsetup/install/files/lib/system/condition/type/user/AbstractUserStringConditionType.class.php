<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\container\PrefixConditionFormFieldContainer;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type Filter = array{condition: string, value: string}
 * @implements IDatabaseObjectListConditionType<UserList<User>, Filter>
 * @implements IObjectConditionType<User, Filter>
 * @extends AbstractConditionType<Filter>
 */
abstract class AbstractUserStringConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $columnName
    ) {
    }

    #[\Override]
    public function getFormField(string $id): PrefixConditionFormFieldContainer
    {
        return PrefixConditionFormFieldContainer::create($id)
            ->field(
                TextFormField::create("{$id}Value")
                    ->required()
            )
            ->prefixField(
                SingleSelectionFormField::create("{$id}Condition")
                    ->options($this->getConditions())
                    ->required()
            );
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    #[\Override]
    public function getLabel(): string
    {
        return "wcf.condition.user.{$this->identifier}";
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        ["condition" => $condition, "value" => $value] = $this->filter;
        $filter = match ($condition) {
            "_%" => $value . '%',
            "%_%" => '%' . $value . '%',
            "%_" => '%' . $value,
            default => '',
        };

        $objectList->getConditionBuilder()->add(
            $objectList->getDatabaseTableAlias() . ".{$this->columnName} LIKE ?",
            [$filter]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        ["condition" => $condition, "value" => $value] = $this->filter;

        return match ($condition) {
            "_%" => \str_starts_with($object->{$this->columnName}, $value),
            "%_%" => \str_contains($object->{$this->columnName}, $value),
            "%_" => \str_ends_with($object->{$this->columnName}, $value),
            default => false,
        };
    }

    /**
     * @return array<string, string>
     */
    private function getConditions(): array
    {
        return [
            "_%" => "wcf.condition.startsWith",
            "%_%" => "wcf.condition.contains",
            "%_" => "wcf.condition.endsWith",
        ];
    }
}
