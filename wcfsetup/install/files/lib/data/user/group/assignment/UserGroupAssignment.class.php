<?php

namespace wcf\data\user\group\assignment;

use wcf\data\DatabaseObject;
use wcf\data\user\group\UserGroup;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\util\JSON;

/**
 * Represents an automatic assignment to a user group.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $assignmentID       unique id of the automatic user group assignment
 * @property-read   int $groupID        id of the user group to which users are automatically assigned
 * @property-read   string $title          title of the automatic user group assignment
 * @property-read   int $isDisabled     is `1` if the user group assignment is disabled and thus not checked for automatic assignments, otherwise `0`
 * @property-read string $conditions JSON-encoded string containing the conditions of the automatic user group assignment
 *
 * @phpstan-import-type ConditionValue from ConditionHandler
 */
class UserGroupAssignment extends DatabaseObject implements IRouteController
{
    /**
     * Returns the conditions of the automatic assignment to a user group.
     *
     * @return array{identifier: string, value: ConditionValue}[]
     */
    public function getConditions(): array
    {
        return JSON::decode($this->conditions);
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Returns the user group the automatic assignment belongs to.
     *
     * @return  UserGroup
     */
    public function getUserGroup()
    {
        return UserGroup::getGroupByID($this->groupID);
    }
}
