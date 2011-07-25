<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Provides a password input form element.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
class PasswordInputFormElement extends AbstractNamedFormElement {
	/**
	 * @see	wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<div class="formElement">
	<div class="formFieldLabel">
		<label for="{$this->getName()}">{$this->getLabel()}</label>
	</div>
	<div class="formField">
		<input type="password" name="{$formName}{$this->getName()}" id="{$this->getName()}" value="{$this->getValue()}" />
	</div>
	<div class="formFieldDesc">
		<p>{$this->getDescription()}</p>
	</div>
</div>
HTML;
	}
}
