<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents an list of user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category	Community Framework
 *
 * @method	UserOptionCategory		current()
 * @method	UserOptionCategory[]		getObjects()
 * @method	UserOptionCategory|null		search($objectID)
 * @property	UserOptionCategory[]		$objects
 */
class UserOptionCategoryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = UserOptionCategory::class;
}
