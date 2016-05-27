<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 *
 * @method	UserGroupOptionCategory		current()
 * @method	UserGroupOptionCategory[]	getObjects()
 * @method	UserGroupOptionCategory|null	search($objectID)
 * @property	UserGroupOptionCategory[]	$objects
 */
class UserGroupOptionCategoryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserGroupOptionCategory::class;
}
