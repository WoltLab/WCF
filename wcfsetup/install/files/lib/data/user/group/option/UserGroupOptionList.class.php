<?php
namespace wcf\data\user\group\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option
 * @category	Community Framework
 */
class UserGroupOptionList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\group\option\UserGroupOption';
}
