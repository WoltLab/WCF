<?php
namespace wcf\system\form\builder;
use wcf\data\object\type\ObjectType;
use wcf\system\exception\InvalidObjectTypeException;

/**
 * Represents a form node that relies on a specific object type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IObjectTypeFormNode {
	/**
	 * Returns the object type.
	 * 
	 * @return	ObjectType			object type
	 *
	 * @throws	\BadMethodCallException		if object type has not been set
	 */
	public function getObjectType();
	
	/**
	 * Sets the name of the object type and returns this field.
	 *
	 * @param	string		$objectType	object type name
	 * @return	IObjectTypeFormNode		this field
	 *
	 * @throws	\BadMethodCallException		if object type has already been set
	 * @throws	\UnexpectedValueException	if object type definition returned by `getObjectTypeDefinition()` is unknown
	 * @throws	InvalidObjectTypeException	if given object type name is invalid
	 */
	public function objectType($objectType);
	
	/**
	 * Returns the name of the object type definition the set object type must be of.
	 * 
	 * @return	string		name of object type's definition
	 */
	public function getObjectTypeDefinition();
}
