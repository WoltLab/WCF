<?php
namespace wcf\system\form\element;

/**
 * Provides a password input form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
class PasswordInputFormElement extends AbstractNamedFormElement {
	/**
	 * @see	\wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<dl{$this->getErrorClass()}>
	<dt><label for="{$this->getName()}">{$this->getLabel()}</label></dt>
	<dd>
		<input type="password" id="{$this->getName()}" name="{$formName}{$this->getName()}" value="{$this->getValue()}" class="medium" />
		<small>{$this->getDescription()}</small>
		{$this->getErrorField()}
	</dd>
</dl>
HTML;
	}
}
