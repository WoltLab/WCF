<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for integer values.
 * 
 * If a non-required integer field is left empty, its value is `null`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class IntegerFormField extends AbstractFormField implements IMaximumFormField, IMinimumFormField, INullableFormField, IPlaceholderFormField, ISuffixedFormField {
	use TMaximumFormField;
	use TMinimumFormField;
	use TNullableFormField;
	use TPlaceholderFormField;
	use TSuffixedFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__integerFormField';
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->getValue() === null && !$this->isNullable()) {
			return 0;
		}
		
		return parent::getSaveValue();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if (isset($_POST[$this->getPrefixedId()]) && $_POST[$this->getPrefixedId()] !== '') {
			$this->__value = intval($_POST[$this->getPrefixedId()]);
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->getValue() === null) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		else {
			if ($this->getMinimum() !== null && $this->getValue() < $this->getMinimum()) {
				$this->addValidationError(new FormFieldValidationError('minimum', 'wcf.global.form.integer.error.minimum', [
					'minimum' => $this->getMinimum()
				]));
			}
			else if ($this->getMaximum() !== null && $this->getValue() > $this->getMaximum()) {
				$this->addValidationError(new FormFieldValidationError('maximum', 'wcf.global.form.integer.error.maximum', [
					'maximum' => $this->getMaximum()
				]));
			}
		}
		
		parent::validate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value): IFormField {
		if ($value !== null && !is_int($value)) {
			throw new \InvalidArgumentException("Given value is neither `null` nor an integer, " . gettype($value) . " given.");
		}
		
		return parent::value($value);
	}
}
