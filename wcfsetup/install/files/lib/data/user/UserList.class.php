<?php
namespace wcf\data\user;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of users.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category 	Community Framework
 */
class UserList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\User';
}
