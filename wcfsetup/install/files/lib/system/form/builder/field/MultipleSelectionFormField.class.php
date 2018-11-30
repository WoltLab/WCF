<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for selecting multiple values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class MultipleSelectionFormField extends AbstractFormField implements INullableFormField, ISelectionFormField {
	use TNullableFormField;
	use TSelectionFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__multipleSelectionFormField';
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_array($value)) {
				$this->__value = $value;
			}
			else if (!$this->isNullable()) {
				$this->__value = [];
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$value = $this->getValue();
		
		if (($value === null || empty($value)) && $this->isRequired()) {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else if ($value !== null && !empty(array_diff($this->getValue(), array_keys($this->getOptions())))) {
			$this->addValidationError(new FormFieldValidationError(
				'invalidValue',
				'wcf.global.form.error.noValidSelection'
			));
		}
		
		parent::validate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		// ignore `null` as value which can be passed either for nullable
		// fields or as value if no options are available
		if ($value === null) {
			return $this;
		}
		
		if (!is_array($value)) {
			throw new \InvalidArgumentException("Given value is no array, " . gettype($value) . " given.");
		}
		
		$unknownValues = array_diff($value, array_keys($this->getOptions()));
		if (!empty($unknownValues)) {
			throw new \InvalidArgumentException("Unknown values '" . implode("', '", $unknownValues) . '"');
		}
		
		return parent::value($value);
	}
}
