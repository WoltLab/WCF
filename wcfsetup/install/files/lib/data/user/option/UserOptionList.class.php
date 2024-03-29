<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserOption      current()
 * @method  UserOption[]        getObjects()
 * @method  UserOption|null     getSingleObject()
 * @method  UserOption|null     search($objectID)
 * @property    UserOption[] $objects
 */
class UserOptionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = UserOption::class;
}
