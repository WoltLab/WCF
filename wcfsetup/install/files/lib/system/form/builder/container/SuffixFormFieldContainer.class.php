<?php
namespace wcf\system\form\builder\container;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\ISelectionFormField;
use wcf\system\WCF;

/**
 * Represents a form field container for one main field with (optional) support for a suffix selection
 * form field.
 * 
 * Child elements explicitly added to this container are not shown.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
class SuffixFormFieldContainer extends FormContainer {
	/**
	 * form field to which the suffix selection is added
	 * @var	IFormField
	 */
	protected $field;
	
	/**
	 * selection form field containing the suffix options
	 * @var	ISelectionFormField
	 */
	protected $suffixField;
	
	/**
	 * Sets the form field to which the suffix selection is added and returns this field.
	 * 
	 * @param	IFormField	$formField
	 * @return	$this
	 */
	public function field(IFormField $formField) {
		if ($this->field !== null) {
			throw new \BadMethodCallException('Field has already been set.');
		}
		
		$this->field = $formField;
		$this->appendChild($formField);
		
		return $this;
	}
	
	/**
	 * Returns the form field to which the suffix selection is added.
	 * 
	 * @return	IFormField
	 */
	public function getField() {
		if ($this->field === null) {
			throw new \BadMethodCallException('Field has not been set yet.');
		}
		
		return $this->field;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch('__suffixFormFieldContainer', 'wcf', [
			'element' => $this
		]);
	}
	
	/**
	 * Returns the initial option of the suffix selection dropdown.
	 * 
	 * @return	array
	 * @throws	\BadMethodCallException		if no suffix field is set or has no options
	 */
	public function getSelectedSuffixOption() {
		if ($this->getSuffixField() === null) {
			throw new \BadMethodCallException('There is no suffix field for which a label could be determined.');
		}
		if (empty($this->getSuffixField()->getOptions())) {
			throw new \BadMethodCallException('The suffix field has no options.');
		}
		
		foreach ($this->getSuffixField()->getNestedOptions() as $option) {
			if ($this->getSuffixField()->getValue() === null) {
				if ($option['isSelectable']) {
					return $option;
				}
			}
			else if ($option['value'] == $this->getSuffixField()->getValue()) {
				return $option;
			}
		}
		
		// Return the first selectable option if no valid value is selected.
		foreach ($this->getSuffixField()->getNestedOptions() as $option) {
			if ($option['isSelectable']) {
				return $option;
			}
		}
		
		throw new \RuntimeException('Cannot determine selected suffix option.');
	}
	
	/**
	 * Returns the selection form field containing the suffix options.
	 * 
	 * @return	ISelectionFormField
	 */
	public function getSuffixField() {
		return $this->suffixField;
	}
	
	/**
	 * Returns the label used for the suffix selection if the field has no selectable options
	 * or is immutable.
	 *
	 * @return	string
	 */
	public function getSuffixLabel() {
		if ($this->getSuffixField() === null) {
			throw new \BadMethodCallException('There is no suffix field for which a label could be determined.');
		}
		
		if (empty($this->getSuffixField()->getOptions())) {
			return '';
		}
		
		if (isset($this->getSuffixField()->getOptions()[$this->getSuffixField()->getValue()])) {
			return $this->getSuffixField()->getOptions()[$this->getSuffixField()->getValue()];
		}
		
		return '';
	}
	
	/**
	 * Sets the selection form field containing the suffix options.
	 *
	 * @param	ISelectionFormField	$formField
	 * @return	$this
	 * @throws	\BadMethodCallException		if no suffix field is set
	 */
	public function suffixField(ISelectionFormField $formField) {
		if ($this->suffixField !== null) {
			throw new \BadMethodCallException('Suffix field has already been set.');
		}
		
		$this->suffixField = $formField;
		$this->appendChild($formField);
		
		return $this;
	}
	
	/**
	 * Returns `true` if the suffix selection has any selectable options.
	 * 
	 * @return	bool
	 */
	public function suffixHasSelectableOptions() {
		if ($this->getSuffixField() === null) {
			return false;
		}
		
		if ($this->getSuffixField() instanceof IImmutableFormField && $this->getSuffixField()->isImmutable()) {
			return false;
		}
		
		foreach ($this->getSuffixField()->getNestedOptions() as $option) {
			if ($option['isSelectable']) {
				return true;
			}
		}
		
		return false;
	}
}
