<?php
namespace wcf\data\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option.category
 * @category	Community Framework
 */
class OptionCategoryList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\option\category\OptionCategory';
}
