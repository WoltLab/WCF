<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @implements  IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 */
final class UsernameConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): IFormField
    {
        return TextFormField::create($id);
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
    public function applyFilter(DatabaseObjectList $objectList, float|int|string $filter): void
    {
        $objectList->getConditionBuilder()->add(
            $objectList->getDatabaseTableAlias() . '.username LIKE ?',
            ['%' . $filter . '%']
        );
    }

    #[\Override]
    public function match(object $object, float|int|string $filter): bool
    {
        return \str_contains($object->getUsername(), $filter);
    }
}
