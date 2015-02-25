<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of tags.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.tag
 * @category	Community Framework
 */
class TagList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\tag\Tag';
}
