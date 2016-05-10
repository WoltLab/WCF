<?php
namespace wcf\data\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option.category
 * @category	Community Framework
 *
 * @method	OptionCategory		current()
 * @method	OptionCategory[]	getObjects()
 * @method	OptionCategory|null	search($objectID)
 * @property	OptionCategory[]	$objects
 */
class OptionCategoryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = OptionCategory::class;
}
