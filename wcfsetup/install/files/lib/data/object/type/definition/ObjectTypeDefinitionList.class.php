<?php
namespace wcf\data\object\type\definition;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of object type definitions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type\Definition
 *
 * @method	ObjectTypeDefinition		current()
 * @method	ObjectTypeDefinition[]		getObjects()
 * @method	ObjectTypeDefinition|null	search($objectID)
 * @property	ObjectTypeDefinition[]		$objects
 */
class ObjectTypeDefinitionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ObjectTypeDefinition::class;
}
