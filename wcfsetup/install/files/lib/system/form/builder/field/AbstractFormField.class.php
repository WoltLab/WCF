<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\IFormFieldValidationError;
use wcf\system\form\builder\field\validation\IFormFieldValidator;
use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormElement;
use wcf\system\WCF;

/**
 * Abstract implementation of a form field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
abstract class AbstractFormField implements IFormField {
	use TFormChildNode;
	use TFormElement;
	
	/**
	 * `true` if this field is auto-focused and `false` otherwise
	 * @var	bool
	 */
	protected $__autoFocus = false;
	
	/**
	 * name of the object property this field represents
	 * @var	null|string
	 */
	protected $__objectProperty;
	
	/**
	 * `true` if this field has to be filled out and returns `false` otherwise
	 * @var	bool
	 */
	protected $__required = false;
	
	/**
	 * value of the field
	 * @var	mixed
	 */
	protected $__value;
	
	/**
	 * name of the template used to output this field
	 * @var	string
	 */
	protected $templateName;
	
	/**
	 * validation errors of this field
	 * @var	IFormFieldValidationError[]
	 */
	protected $validationErrors = [];
	
	/**
	 * field value validators of this field
	 * @var	IFormFieldValidator[]
	 */
	protected $validators = [];
	
	/**
	 * @inheritDoc
	 */
	public function addValidationError(IFormFieldValidationError $error) {
		if (empty($this->validationErrors)) {
			$this->addClass('formError');
		}
		
		$this->validationErrors[] = $error;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addValidator(IFormFieldValidator $validator) {
		if ($this->hasValidator($validator->getId())) {
			throw new \InvalidArgumentException("Validator with id '{$validator->getId()}' already exists.");
		}
		
		$this->validators[$validator->getId()] = $validator;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function autoFocus($autoFocus = true) {
		$this->__autoFocus = $autoFocus;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		if ($this->templateName === null) {
			throw new \LogicException("\$templateName property has not been set.");
		}
		
		if ($this->requiresLabel() && $this->getLabel() === null) {
			throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
		}
		
		return WCF::getTPL()->fetch(
			$this->templateName,
			'wcf',
			array_merge($this->getHtmlVariables(), ['field' => $this]),
			true
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectProperty() {
		if ($this->__objectProperty !== null) {
			return $this->__objectProperty;
		}
		
		return $this->getId();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		return $this->getValue();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getValidationErrors() {
		return $this->validationErrors;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getValidators() {
		return $this->validators;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return $this->__value;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasValidator($validatorId) {
		FormFieldValidator::validateId($validatorId);
		
		return isset($this->validators[$validatorId]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAutoFocused() {
		return $this->__autoFocus;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isRequired() {
		return $this->__required;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValueFromObject(IStorableObject $object) {
		if (isset($object->{$this->getObjectProperty()})) {
			$this->value($object->{$this->getObjectProperty()});
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public function objectProperty($objectProperty) {
		if ($objectProperty === '') {
			$this->__objectProperty = null;
		}
		else {
			static::validateId($objectProperty);
			
			$this->__objectProperty = $objectProperty;
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeValidator($validatorId) {
		if (!$this->hasValidator($validatorId)) {
			throw new \InvalidArgumentException("Unknown validator with id '{$validatorId}'");
		}
		
		unset($this->validators[$validatorId]);
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public function required($required = true) {
		$this->__required = $required;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		$this->__value = $value;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// does nothing
	}
}
