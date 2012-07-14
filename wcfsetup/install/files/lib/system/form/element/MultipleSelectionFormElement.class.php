<?php
namespace wcf\system\form\element;

/**
 * Provides a checkbox form element.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category 	Community Framework
 */
class MultipleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * @see	wcf\system\form\element\AbstractNamedFormElement::setValue()
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
	 * @see	wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<label><input type="checkbox" name="{$formName}{$this->getParent()->getName()}[]" value="{$this->getValue()}" /> {$this->getLabel()}</label>
HTML;
	}
}
