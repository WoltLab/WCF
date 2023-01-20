<?php

namespace wcf\data\user\avatar;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of avatars.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserAvatar      current()
 * @method  UserAvatar[]        getObjects()
 * @method  UserAvatar|null     getSingleObject()
 * @method  UserAvatar|null     search($objectID)
 * @property    UserAvatar[] $objects
 */
class UserAvatarList extends DatabaseObjectList
{
}
