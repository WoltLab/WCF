<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type.definition
 * @category	Community Framework
 *
 * @method	ObjectType		current()
 * @method	ObjectType[]		getObjects()
 * @method	ObjectType|null		search($objectID)
 * @property	ObjectType[]		$objects
 */
class ObjectTypeList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ObjectType::class;
}
