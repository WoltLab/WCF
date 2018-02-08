<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;
use wcf\system\tagging\TagEngine;
use wcf\util\ArrayUtil;

/**
 * Implementation of a form field for tags.
 * 
 * This field uses the `wcf.tagging.tags` and `wcf.tagging.tags.description` language
 * item as the default form field label and description, respectively.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class TagFormField extends AbstractFormField {
	/**
	 * taggable object type
	 * @var	null|ObjectType
	 */
	protected $__objectType;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__tagFormField';
	
	/**
	 * Creates a new instance of `TagFormField`.
	 */
	public function __construct() {
		$this->description('wcf.tagging.tags.description');
		$this->label('wcf.tagging.tags');
	}
	
	/**
	 * Returns the taggable object type.
	 * 
	 * @return	ObjectType			taggable object type
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
		$objectID = $object->{$object::getDatabaseTableIndexName()};
		
		if ($objectID === null) {
			throw new \UnexpectedValueException("Cannot read object id from object of class '" . get_class($object). "'.");
		}
		
		if ($this->getObjectType() === null) {
			throw new \UnexpectedValueException("Missing taggable object type.");
		}
		
		$languageIDs = [];
		if ($object->languageID !== null) {
			$languageIDs[] = $object->languageID;
		}
		
		$tags = TagEngine::getInstance()->getObjectTags($this->getObjectType()->objectType, $objectID, $languageIDs);
		
		$this->__value = [];
		foreach ($tags as $tag) {
			$this->__value[] = $tag->name;
		}
		
		return $this;
	}
	
	/**
	 * Sets the name of the taggable object type and returns this field.
	 * 
	 * @param	string		$objectType	taggable object type name
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
			$this->__objectType = ObjectTypeCache::getInstance()->getObjectType(TagEngine::getInstance()->getObjectTypeID($objectType));
		}
		catch (InvalidObjectTypeException $e) {
			throw new \InvalidArgumentException("Given object type name is no valid taggable object type.");
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('acl', function(IFormDocument $document, array $parameters) {
			if ($this->getValue() !== null && !empty($this->getValue())) {
				$parameters['tags'] = $this->getValue();
			}
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if (isset($_POST[$this->getPrefixedId()]) && is_array($_POST[$this->getPrefixedId()])) {
			$this->__value = ArrayUtil::trim($_POST[$this->getPrefixedId()]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		if (!is_array($value)) {
			throw new \InvalidArgumentException("Given value is no array, " . gettype($value) . " given.");
		}
		
		$stringTags = [];
		$stringValues = null;
		
		foreach ($value as $tag) {
			if (is_string($tag)) {
				if ($stringValues === null) {
					$stringValues = true;
				}
				
				if ($stringValues === false) {
					throw new \InvalidArgumentException("Given value array contains mixed values, all values have to be either strings or `" . Tag::class . "` objects.");
				}
				
				$stringTags[] = $tag;
			}
			else if ($tag instanceof Tag) {
				if ($stringValues === null) {
					$stringValues = false;
				}
				
				if ($stringValues === true) {
					throw new \InvalidArgumentException("Given value array contains mixed values, all values have to be either strings or `" . Tag::class . "` objects.");
				}
				
				$stringTags[] = $tag->name;
			}
			else {
				throw new \InvalidArgumentException("Given value array contains invalid value of type " . gettype($tag) . ".");
			}
		}
		
		return parent::value($stringTags);
	}
}
