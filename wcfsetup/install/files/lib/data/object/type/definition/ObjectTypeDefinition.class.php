<?php
namespace wcf\data\object\type\definition;
use wcf\data\DatabaseObject;

/**
 * Represents an object type definition.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type\Definition
 *
 * @property-read	integer		$definitionID		unique id of the object type definition
 * @property-read	string		$definitionName		textual identifier of the object type definition
 * @property-read	integer		$packageID		id of the package the which delivers the object type definition
 * @property-read	string		$interfaceName		PHP interface name the PHP classes of the object types' processors need to implement
 * @property-read	string		$categoryName		
 */
class ObjectTypeDefinition extends DatabaseObject {}
