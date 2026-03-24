<?php

namespace wcf\system\option\user\group;

use wcf\data\user\group\UserGroup;

/**
 * Default trait for user group option types implementing IUserGroupGroupOptionType.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TUserGroupOptionType
{
    /**
     * user group object
     * @var UserGroup
     */
    protected $userGroup;

    #[\Override]
    public function setUserGroup(UserGroup $group)
    {
        $this->userGroup = $group;
    }

    #[\Override]
    public function getUserGroup()
    {
        return $this->userGroup;
    }
}
