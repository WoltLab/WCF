<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\acl\ACLHandler;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for setting acl option values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class AclFormField extends AbstractFormField {
	/**
	 * name of/filter for the name(s) of the shown acl option categories 
	 * @var	null|string
	 */
	protected $__categoryName;
	
	/**
	 * acl object type
	 * @var	null|ObjectType
	 */
	protected $__objectType;
	
	/**
	 * id of the edited object or `null` if no object is edited
	 * @var	null|int
	 */
	protected $objectID;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__aclFormField';
	
	/**
	 * is `true` if acl-related global JavaScript code has already been included
	 * and is `false` otherwise
	 * @var	bool
	 */
	protected static $includedAclJavaScript = false;
	
	/**
	 * Sets the name of/filter for the name(s) of the shown acl option categories and
	 * returns this field.
	 * 
	 * @param	string		$categoryName	name of/filter for the acl option categories
	 * @return	static		$this		this field
	 * 
	 * @throws	\InvalidArgumentException	if given category name is no string or otherwise invalid
	 */
	public function categoryName($categoryName) {
		if (!is_string($categoryName)) {
			throw new \InvalidArgumentException("Given category name is no string, '" . gettype($categoryName) . "' given.");
		}
		
		$this->__categoryName = $categoryName;
		
		return $this;
	}
	
	/**
	 * Returns the name of/filter for the name(s) of the shown acl option categories
	 * or returns `null` if no category name has been set.
	 * 
	 * @return	null|string
	 */
	public function getCategoryName() {
		return $this->__categoryName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables() {
		ACLHandler::getInstance()->assignVariables($this->getObjectType()->objectTypeID);
		
		$includeAclJavaScript = !static::$includedAclJavaScript;
		if (!static::$includedAclJavaScript) {
			static::$includedAclJavaScript = true;
		}
		
		return [
			'includeAclJavaScript' => $includeAclJavaScript
		];
	}
	
	/**
	 * Returns the id of the edited object or `null` if no object is edited.
	 * 
	 * @return	null|int
	 */
	public function getObjectID() {
		return $this->objectID;
	}
	
	/**
	 * Returns the acl object type. 
	 * 
	 * @return	ObjectType			acl object type
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
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValueFromObject(IStorableObject $object) {
		$this->objectID = $object->{$object::getDatabaseTableIndexName()};
		
		if ($this->objectID === null) {
			throw new \UnexpectedValueException("Cannot read object id from object of class '" . get_class($object). "'.");
		}
	}
	
	/**
	 * Sets the name of the acl object type and returns this field.
	 * 
	 * @param	string		$objectType	acl object type name
	 * @return	static				this field
	 * 
	 * @throws	\BadMethodCallException		if object type has already been set
	 * @throws	\InvalidArgumentException	if given object type name is no string or otherwise invalid
	 */
	public function objectType($objectType) {
		if ($this->__objectType !== null) {
			throw new \BadMethodCallException("Object type has already been set.");
		}
		
		if (!is_string($objectType)) {
			throw new \InvalidArgumentException("Given object type name is no string, '" . gettype($objectType) . "' given.");
		}
		
		try {
			$this->__objectType = ObjectTypeCache::getInstance()->getObjectType(ACLHandler::getInstance()->getObjectTypeID($objectType));
		}
		catch (SystemException $e) {
			throw new \InvalidArgumentException("Given object type name is no valid acl object type.");
		}
		
		// reset old values from previous request
		ACLHandler::getInstance()->resetValues($this->__objectType->objectTypeID);
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('acl', function(IFormDocument $document, array $parameters) {
			$parameters['aclObjectTypeID'] = $this->getObjectType()->objectTypeID;
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		ACLHandler::getInstance()->readValues($this->getObjectType()->objectTypeID);
	}
}
