<?php
namespace wcf\system\form\element;

/**
 * Provides a checkbox form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
class MultipleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * message displayed if the input is disabled
	 * @var	string
	 */
	protected $disabledMessage = '';
	
	/**
	 * @see	\wcf\system\form\element\AbstractNamedFormElement::setValue()
	 */
	public function setValue($value) {
		if (!is_array($value)) {
			parent::setValue($value);
		}
		else {
			$this->value = array_map(array('wcf\util\StringUtil', 'trim'), $value);
		}
	}
	
	/**
	 * Sets message displayed if input should be disabled.
	 * 
	 * @param	string		$message
	 */
	public function setDisabledMessage($message) {
		$this->disabledMessage = $message;
	}
	
	/**
	 * @see	\wcf\system\form\element\AbstractNamedFormElement::getDescription()
	 */
	public function getDescription() {
		if ($this->disabledMessage) {
			return $this->disabledMessage;
		}
		
		return parent::getDescription();
	}
	
	/**
	 * @see	\wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		$disabled = '';
		if ($this->disabledMessage) {
			$disabled = ' disabled="disabled"';
		}
		
		return <<<HTML
<label><input type="checkbox" name="{$formName}{$this->getParent()->getName()}[]" value="{$this->getValue()}"{$disabled} /> {$this->getLabel()}</label>
<small>{$this->getDescription()}</small>
HTML;
	}
}
