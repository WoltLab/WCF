<?php
namespace wcf\data\user\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group
 *
 * @method	UserGroup		current()
 * @method	UserGroup[]		getObjects()
 * @method	UserGroup|null		search($objectID)
 * @property	UserGroup[]		$objects
 */
class UserGroupList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserGroup::class;
}
