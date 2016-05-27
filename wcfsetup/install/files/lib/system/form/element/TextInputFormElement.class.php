<?php
namespace wcf\system\form\element;

/**
 * Provides a text input form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
class TextInputFormElement extends AbstractNamedFormElement {
	/**
	 * @inheritDoc
	 */
	public function getHTML($formName) {
		return <<<HTML
<dl{$this->getErrorClass()}>
	<dt><label for="{$this->getName()}">{$this->getLabel()}</label></dt>
	<dd>
		<input type="text" id="{$this->getName()}" name="{$formName}{$this->getName()}" value="{$this->getValue()}" class="long" />
		<small>{$this->getDescription()}</small>
		{$this->getErrorField()}
	</dd>
</dl>
HTML;
	}
}
