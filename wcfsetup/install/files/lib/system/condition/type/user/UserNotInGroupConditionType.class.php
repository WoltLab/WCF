<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\SingleSelectionFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 * @extends AbstractConditionType<int>
 */
final class UserNotInGroupConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): SingleSelectionFormField
    {
        return SingleSelectionFormField::create($id)
            ->options(
                UserGroup::getGroupsByType(invalidGroupTypes: [
                    UserGroup::EVERYONE,
                    UserGroup::GUESTS,
                    UserGroup::USERS,
                ])
            );
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'notInGroup';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.notInGroup';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.userID NOT IN (
                    SELECT userID
                    FROM   wcf1_user_to_group
                    WHERE  groupID = ?
                )",
            [$this->filter]
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        return !\in_array($this->filter, $object->getGroupIDs());
    }
}
