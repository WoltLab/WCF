<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type.definition
 * @category	Community Framework
 */
class ObjectTypeList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\object\type\ObjectType';
}
