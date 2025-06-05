<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
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
abstract class AbstractUserBooleanConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $columnName
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
}
