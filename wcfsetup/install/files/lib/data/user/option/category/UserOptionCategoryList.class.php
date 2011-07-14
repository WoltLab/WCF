<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectList;
use wcf\system\database\util\ConditionBuilder;
use wcf\system\WCF;

/**
 * Represents an list of user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategoryList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\user\option\category\UserOptionCategory';
}
?>