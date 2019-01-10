<?php
namespace wcf\system\form\builder\field\tag;
use wcf\data\tag\Tag;
use wcf\data\IStorableObject;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\data\processor\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\IObjectTypeFormField;
use wcf\system\form\builder\field\TObjectTypeFormField;
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
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Tag
 * @since	5.2
 */
class TagFormField extends AbstractFormField implements IObjectTypeFormField {
	use TObjectTypeFormField;
	
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
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition() {
		return 'com.woltlab.wcf.tagging.taggableObject';
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
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('acl', function(IFormDocument $document, array $parameters) {
			if ($this->checkDependencies() && $this->getValue() !== null && !empty($this->getValue())) {
				$parameters[$this->getObjectProperty()] = $this->getValue();
			}
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_array($value)) {
				$this->__value = ArrayUtil::trim($value);
			}
		}
		
		return $this;
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
