<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\SelectFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, string>
 * @implements IObjectConditionType<User, string>
 * @extends AbstractConditionType<string>
 */
final class UserInGroupConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): SelectFormField
    {
        // SelectFormField stores its value as a string,
        // so we need to convert it to an integer in the `applyFilter`&`matches` method.
        return SelectFormField::create($id)
            ->options(
                UserGroup::getGroupsByType(invalidGroupTypes: [
                    UserGroup::EVERYONE,
                    UserGroup::GUESTS,
                    UserGroup::USERS,
                ])
            )
            ->required();
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'inGroup';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.inGroup';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.userID IN (
                    SELECT userID
                    FROM   wcf1_user_to_group
                    WHERE  groupID = ?
            )",
            [(int)$this->filter]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        return \in_array((int)$this->filter, $object->getGroupIDs(), true);
    }
}
