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
use wcf\system\form\builder\field\IntegerFormField;
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
class IntegerUserConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType, IMigrateConditionType
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $columnName,
        public readonly string $category,
        public readonly ?string $migrateConditionObjectType = null,
    ) {
    }

    #[\Override]
    public function getFormField(string $id): PrefixConditionFormFieldContainer
    {
        return PrefixConditionFormFieldContainer::create($id)
            ->field(
                IntegerFormField::create("{$id}Value")
                    ->minimum(0)
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
        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.{$this->columnName} {$this->filter['condition']} ?",
            [$this->filter['value']]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        return match ($this->filter['condition']) {
            '=' => $object->{$this->columnName} == $this->filter['value'],
            '>' => $object->{$this->columnName} > $this->filter['value'],
            '<' => $object->{$this->columnName} < $this->filter['value'],
            '>=' => $object->{$this->columnName} >= $this->filter['value'],
            '<=' => $object->{$this->columnName} <= $this->filter['value'],
            default => throw new \InvalidArgumentException("Unknown condition: {$this->filter['condition']}"),
        };
    }

    #[\Override]
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string[]
     */
    protected function getConditions(): array
    {
        return ["=", ">", "<", ">=", "<="];
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === $this->migrateConditionObjectType;
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $lessThan = $conditionData['lessThan'] ?? null;
        $greaterThan = $conditionData['greaterThan'] ?? null;
        $conditions = [];

        if ($lessThan !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => ["value" => $lessThan, 'condition' => '<'],
            ];
        }
        if ($greaterThan !== null) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => ["value" => $greaterThan, 'condition' => '>'],
            ];
        }

        unset($conditionData['lessThan'], $conditionData['greaterThan']);

        return $conditions;
    }
}
