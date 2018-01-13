<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\I18nHandler;
use wcf\util\StringUtil;

/**
 * Provides default implementations of `II18nFormField` methods and other i18n-related methods.
 * 
 * This trait can only to be used in combination with `TFormField`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TI18nFormField {
	/**
	 * `true` if this field supports i18n input and `false` otherwise
	 * @var	bool
	 */
	protected $__i18n = false;
	
	/**
	 * `true` if this field requires i18n input and `false` otherwise
	 * @var	bool
	 */
	protected $__i18nRequired;
	
	/**
	 * Returns additional template variables used to generate the html representation
	 * of this node.
	 * 
	 * @return	array		additional template variables
	 */
	public function getHtmlVariables() {
		if ($this->isI18n()) {
			I18nHandler::getInstance()->assignVariables();
			
			return [
				'elementIdentifier' => $this->getPrefixedId(),
				'forceSelection' => $this->isI18nRequired()
			];
		}
		
		return [];
	}
	
	/**
	 * Returns the value of this field or `null` if no value has been set.
	 *
	 * @return	mixed
	 */
	public function getValue() {
		if ($this->isI18n()) {
			if ($this->hasPlainValue()) {
				return I18nHandler::getInstance()->getValue($this->getPrefixedId());
			}
			else if ($this->hasI18nValues()) {
				return I18nHandler::getInstance()->getValues($this->getPrefixedId());
			}
			
			return '';
		}
		
		return $this->__value;
	}
	
	/**
	 * Returns `true` if the current field value is a i18n value and returns `false`
	 * otherwise or if no value has been set.
	 * 
	 * @return	bool
	 */
	public function hasI18nValues() {
		return I18nHandler::getInstance()->hasI18nValues($this->getPrefixedId());
	}
	
	/**
	 * Returns `true` if the current field value is a plain value and returns `false`
	 * otherwise or if no value has been set.
	 * 
	 * @return	bool
	 */
	public function hasPlainValue() {
		return I18nHandler::getInstance()->isPlainValue($this->getPrefixedId());
	}
	
	/**
	 * Returns `true` if this field provides a value that can simply be stored
	 * in a column of the database object's database table and returns `false`
	 * otherwise.
	 * 
	 * Note: If `false` is returned, this field should probabily add its own
	 * `IFormFieldDataProcessor` object to the form document's data processor.
	 * A suitable place to add the processor is the `parent()`
	 * 
	 * @return	bool
	 */
	public function hasSaveValue() {
		return !$this->isI18n() || $this->hasPlainValue();
	}
	
	/**
	 * Sets whether this field is supports i18n input and returns this field.
	 * 
	 * @param	bool		$i18n		determined if field is supports i18n input
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given value is no bool
	 */
	public function i18n($i18n = true) {
		if (!is_bool($i18n)) {
			throw new \InvalidArgumentException("Given value is no bool, " . gettype($i18n) . " given.");
		}
		
		$this->__i18n = $i18n;
		
		return $this;
	}
	
	/**
	 * Sets whether this field's value must be i18n input and returns this field.
	 *
	 * If this method sets that the field's value must be i18n input, it also must
	 * ensure that i18n support is enabled.
	 *
	 * @param	bool		$i18nRequired		determined if field value must be i18n input
	 * @return	static					this field
	 *
	 * @throws	\InvalidArgumentException		if the given value is no bool
	 */
	public function i18nRequired($i18nRequired = true) {
		if (!is_bool($i18nRequired)) {
			throw new \InvalidArgumentException("Given value is no bool, " . gettype($i18nRequired) . " given.");
		}
		
		$this->__i18nRequired = $i18nRequired;
		$this->i18n();
		
		return $this;
	}
	
	/**
	 * Returns `true` if this field supports i18n input and returns `false` otherwise.
	 * By default, fields do not support i18n input.
	 * 
	 * @return	bool
	 */
	public function isI18n() {
		return $this->__i18n;
	}
	
	/**
	 * Returns `true` if this field's value must be i18n input and returns `false` otherwise.
	 * By default, fields do not support i18n input.
	 * 
	 * @return	bool
	 */
	public function isI18nRequired() {
		return $this->__i18nRequired;
	}
	
	/**
	 * Is called once after all nodes have been added to the document this node belongs to.
	 * 
	 * This method enables this node to perform actions that require the whole document having
	 * finished constructing itself and every parent-child relationship being established.
	 * 
	 * @return	static				this node
	 * 
	 * @throws	\BadMethodCallException		if this node has already been populated
	 */
	public function populate() {
		if ($this->isI18n()) {
			I18nHandler::getInstance()->unregister($this->getPrefixedId());
			I18nHandler::getInstance()->register($this->getPrefixedId());
			
			/** @var IFormDocument $document */
			$document = $this->getDocument();
			$document->getDataHandler()->add(new CustomFormFieldDataProcessor('i18n', function(IFormDocument $document, array $parameters) {
				if ($this->hasI18nValues()) {
					$parameters[$this->getId() . '_i18n'] = $this->getValue();
				}
				
				return $parameters;
			}));
		}
		
		return $this;
	}
	
	/**
	 * Reads the value of this field from request data and return this field.
	 * 
	 * @return	static		this field
	 */
	public function readValue() {
		if ($this->isI18n()) {
			I18nHandler::getInstance()->readValues();
		}
		else if (isset($_POST[$this->getPrefixedId()]) && is_string($_POST[$this->getPrefixedId()])) {
			$this->__value = StringUtil::trim($_POST[$this->getPrefixedId()]);
		}
		
		return $this;
	}
	
	/**
	 * Sets the value of this field and returns this field.
	 * 
	 * @param	mixed		$value		new field value
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given value is of an invalid type or otherwise is invalid
	 */
	public function value($value) {
		if (!is_string($value)) {
			throw new \InvalidArgumentException("Given value is no string, " . gettype($value) . " given.");
		}
		
		// TODO: check implementation for i18n fields
		
		return parent::value($value);
	}
	
	/**
	 * Validates the node.
	 *
	 * Note: A `IFormParentNode` object may only return `true` if all of its child
	 * nodes are valid. A `IFormField` object is valid if its value is valid.
	 */
	public function validate() {
		if (!I18nHandler::getInstance()->validateValue($this->getPrefixedId(), $this->isI18nRequired(), $this->isRequired())) {
			if ($this->hasPlainValue()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
			else {
				$this->addValidationError(new FormFieldValidationError('multilingual'));
			}
		}
	}
}
