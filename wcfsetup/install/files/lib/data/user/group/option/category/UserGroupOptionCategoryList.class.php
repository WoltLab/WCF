<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 */
class UserGroupOptionCategoryList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\group\option\category\UserGroupOptionCategory';
}
