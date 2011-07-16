<?php
namespace wcf\system\form\element;

/**
 * Provides a radio form element.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
class MultipleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * @see	wcf\system\form\FormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<label><input type="checkbox" name="{$formName}{$this->getParent()->getName()}[]" value="{$this->getValue()}" /> {$this->getLabel()}</label>
HTML;
	}
}
