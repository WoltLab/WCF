<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\BooleanFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, bool>
 * @implements IObjectConditionType<User, bool>
 * @extends AbstractConditionType<bool>
 */
class BooleanUserConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType, IMigrateConditionType
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $columnName,
        public readonly string $category,
        public readonly ?string $migrateKeyName = null,
        public readonly ?string $migrateConditionObjectType = null,
    ) {
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
    public function getFormField(string $id): BooleanFormField
    {
        return BooleanFormField::create($id);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.{$this->columnName} = ?", [1]);
        } else {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.{$this->columnName} = ?", [0]);
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return (bool)$object->{$this->columnName};
        } else {
            return !$object->{$this->columnName};
        }
    }

    #[\Override]
    public function getCategory(): string
    {
        return $this->category;
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $value = $conditionData[$this->columnName] ?? null;
        if ($value === null) {
            return [];
        }

        unset($conditionData[$this->migrateKeyName]);

        return [
            [
                'identifier' => $this->identifier,
                'value' => \boolval($value),
            ],
        ];
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $this->migrateConditionObjectType === $objectType;
    }
}
