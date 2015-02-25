<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of acl option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option.category
 * @category	Community Framework
 */
class ACLOptionCategoryList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acl\option\category\ACLOptionCategory';
}
