<?php
namespace wcf\data\object\type\definition;
use wcf\data\DatabaseObject;

/**
 * Represents an object type definition.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type\Definition
 *
 * @property-read	integer		$definitionID
 * @property-read	string		$definitionName
 * @property-read	integer		$packageID
 * @property-read	string		$interfaceName
 * @property-read	string		$categoryName
 */
class ObjectTypeDefinition extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'object_type_definition';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'definitionID';
}
