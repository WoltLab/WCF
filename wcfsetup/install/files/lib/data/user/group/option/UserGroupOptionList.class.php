<?php

namespace wcf\data\user\group\option;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserGroupOption     current()
 * @method  UserGroupOption[]   getObjects()
 * @method  UserGroupOption|null    getSingleObject()
 * @method  UserGroupOption|null    search($objectID)
 * @property    UserGroupOption[] $objects
 */
class UserGroupOptionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = UserGroupOption::class;
}
