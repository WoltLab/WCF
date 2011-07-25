<?php
namespace wcf\data\user\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user groups.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class UserGroupList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\group\UserGroup';
}
