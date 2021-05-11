<?php

namespace wcf\data\user\group\assignment;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group assignments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Group\Assignment
 *
 * @method  UserGroupAssignment     current()
 * @method  UserGroupAssignment[]       getObjects()
 * @method  UserGroupAssignment|null    getSingleObject()
 * @method  UserGroupAssignment|null    seach($objectID)
 * @property    UserGroupAssignment[] $objects
 */
class UserGroupAssignmentList extends DatabaseObjectList
{
}
