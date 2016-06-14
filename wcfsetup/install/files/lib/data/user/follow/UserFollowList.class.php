<?php
namespace wcf\data\user\follow;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Follow
 *
 * @method	UserFollow		current()
 * @method	UserFollow[]		getObjects()
 * @method	UserFollow|null		search($objectID)
 * @property	UserFollow[]		$objects
 */
class UserFollowList extends DatabaseObjectList { }
