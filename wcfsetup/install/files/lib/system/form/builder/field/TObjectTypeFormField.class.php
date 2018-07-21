<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\InvalidObjectTypeException;

/**
 * Provides default implementations of `IObjectTypeFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TObjectTypeFormField {
	/**
	 * object type
	 * @var	null|ObjectType
	 */
	protected $__objectType;
	
	/**
	 * Returns the object type.
	 * 
	 * @return	ObjectType			object type
	 * 
	 * @throws	\BadMethodCallException		if object type has not been set
	 */
	public function getObjectType() {
		if ($this->__objectType === null) {
			throw new \BadMethodCallException("Object type has not been set.");
		}
		
		return $this->__objectType;
	}
	
	/**
	 * Sets the name of the object type and returns this field.
	 * 
	 * @param	string		$objectType	object type name
	 * @return	static				this field
	 * 
	 * @throws	\BadMethodCallException		if object type has already been set
	 * @throws	\UnexpectedValueException	if object type definition returned by `getObjectTypeDefinition()` is unknown
	 * @throws	InvalidObjectTypeException	if given object type name is invalid
	 */
	public function objectType(string $objectType) {
		if ($this->__objectType !== null) {
			throw new \BadMethodCallException("Object type has already been set.");
		}
		
		if (ObjectTypeCache::getInstance()->getDefinitionByName($this->getObjectTypeDefinition()) === null) {
			throw new \UnexpectedValueException("Unknown definition name '{$this->getObjectTypeDefinition()}'.");
		}
		
		$this->__objectType = ObjectTypeCache::getInstance()->getObjectTypeByName($this->getObjectTypeDefinition(), $objectType);
		if ($this->__objectType === null) {
			throw new InvalidObjectTypeException($objectType, $this->getObjectTypeDefinition());
		}
		
		return $this;
	}
	
	/**
	 * Returns the name of the object type definition the set object type must be of.
	 *
	 * @return	string		name of object type's definition
	 */
	abstract public function getObjectTypeDefinition();
}
