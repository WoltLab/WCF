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
 * @since	3.2
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
	 * `true` if the value of this field is immutable and `false` otherwise
	 * @var	bool
	 */
	protected $__immutable = false;
	
	/**
	 * true` if this field has to be filled out and returns `false` otherwise
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
		if (!is_bool($autoFocus)) {
			throw new \InvalidArgumentException("Given value is no bool, " . gettype($autoFocus) . " given.");
		}
		
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
	public function immutable($immutable = true) {
		if (!is_bool($immutable)) {
			throw new \InvalidArgumentException("Given value is no bool, " . gettype($immutable) . " given.");
		}
		
		$this->__immutable = $immutable;
		
		return $this;
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
	public function isImmutable() {
		return $this->__immutable;
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
		if (isset($object->{$this->getId()})) {
			$this->__value = $object->{$this->getId()};
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeValidator($validatorId) {
		if (!$this->hasValidator($validatorId)) {
			throw new \InvalidArgumentException("Unknown validator with id '{$validatorId}'");
		}
		
		unset($this->validators[$validatorId]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function required($required = true) {
		if (!is_bool($required)) {
			throw new \InvalidArgumentException("Given value is no bool, " . gettype($required) . " given.");
		}
		
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
		foreach ($this->getValidators() as $validator) {
			$validator($this);
		}
	}
}
