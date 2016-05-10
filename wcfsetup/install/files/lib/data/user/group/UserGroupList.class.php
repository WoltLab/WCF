<?php
namespace wcf\data\user\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category	Community Framework
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
