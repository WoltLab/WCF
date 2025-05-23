<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\TextFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 */
final class UsernameConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): TextFormField
    {
        // TODO supports beginns with, ends with and contains
        return TextFormField::create($id)
            ->removeFieldClass('long')
            ->addFieldClass('medium');
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'username';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.username';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        $objectList->getConditionBuilder()->add(
            $objectList->getDatabaseTableAlias() . '.username LIKE ?',
            ['%' . $this->filter . '%']
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        return \str_contains($object->getUsername(), $this->filter);
    }
}
