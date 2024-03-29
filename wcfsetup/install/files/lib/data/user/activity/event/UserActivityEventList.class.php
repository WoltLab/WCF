<?php

namespace wcf\data\user\activity\event;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user activity events.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserActivityEvent       current()
 * @method  UserActivityEvent[]     getObjects()
 * @method  UserActivityEvent|null      getSingleObject()
 * @method  UserActivityEvent|null      search($objectID)
 * @property    UserActivityEvent[] $objects
 */
class UserActivityEventList extends DatabaseObjectList
{
}
