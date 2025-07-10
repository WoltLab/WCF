<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\container\PrefixConditionFormFieldContainer;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\WCF;

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
abstract class AbstractStringUserConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType, IMigrateConditionType
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $columnName,
        public readonly ?string $migrateKeyName = null,
        public readonly ?string $migrateConditionObjectType = null,
    ) {}

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
        $value = WCF::getDB()->escapeLikeValue($value);

        $filter = match ($condition) {
            "_%" => $value . '%',
            "%_%" => '%' . $value . '%',
            "%_" => '%' . $value,
            default => throw new \InvalidArgumentException("Unknown condition: {$condition}"),
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
        $value = \mb_strtolower($value);
        $objectValue = \mb_strtolower($object->{$this->columnName});

        return match ($condition) {
            "_%" => \str_starts_with($objectValue, $value),
            "%_%" => \str_contains($objectValue, $value),
            "%_" => \str_ends_with($objectValue, $value),
            default => throw new \InvalidArgumentException("Unknown condition: {$condition}"),
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

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === $this->migrateConditionObjectType;
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $value = $conditionData[$this->migrateKeyName] ?? null;
        if ($value === null) {
            return [];
        }

        unset($conditionData[$this->migrateKeyName]);

        return [
            [
                'identifier' => $this->identifier,
                'value' => [
                    'condition' => "%_%",
                    'value' => $value,
                ],
            ],
        ];
    }
}
