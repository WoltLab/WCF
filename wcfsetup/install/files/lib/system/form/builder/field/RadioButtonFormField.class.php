<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a radio buttons form field for selecting a single value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class RadioButtonFormField extends AbstractFormField implements IImmutableFormField, ISelectionFormField {
	use TImmutableFormField;
	use TSelectionFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__radioButtonFormField';
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				$this->__value = $value;
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsNestedOptions() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (!isset($this->getOptions()[$this->getValue()])) {
			$this->addValidationError(new FormFieldValidationError(
				'invalidValue',
				'wcf.global.form.error.noValidSelection'
			));
		}
		
		parent::validate();
	}
}
