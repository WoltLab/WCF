<?php
namespace wcf\data\object\type\definition;
use wcf\data\DatabaseObject;

/**
 * Represents an object type definition.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type.definition
 * @category	Community Framework
 *
 * @property-read	integer		$definitionID
 * @property-read	string		$definitionName
 * @property-read	integer		$packageID
 * @property-read	string		$interfaceName
 * @property-read	string		$categoryName
 */
class ObjectTypeDefinition extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'object_type_definition';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'definitionID';
}
