<?php
namespace wcf\system\form\element;

/**
 * Provides a radio form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
class SingleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * @see	\wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<label><input type="radio" name="{$formName}{$this->getParent()->getName()}" value="{$this->getValue()}" /> {$this->getLabel()}</label>
HTML;
	}
}
