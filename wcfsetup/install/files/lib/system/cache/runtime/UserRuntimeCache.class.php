<?php

namespace wcf\system\cache\runtime;

use wcf\data\user\User;
use wcf\data\user\UserList;

/**
 * Runtime cache implementation for users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractRuntimeCache<User, UserList>
 */
class UserRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = UserList::class;
}
