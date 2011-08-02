<?php
namespace wcf\system\form\element;

/**
 * Provides a label form element.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
class LabelFormElement extends AbstractFormElement {
	/**
	 * element text
	 *
	 * @var	string
	 */
	protected $text = '';
	
	/**
	 * Sets element text.
	 *
	 * @param	string		$text
	 */
	public function setText($text) {
		$text->text = StringUtil::trim($text);
	}
	
	/**
	 * Returns element text.
	 *
	 * @return	string
	 */
	public function getText() {
		return $this->text;
	}
	
	/**
	 * @see	wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<div class="formElement">
	<div class="formFieldLabel">
		<label>{$this->getLabel()}</label>
	</div>
	<div class="formField">
		{$this->getText()}
	</div>
	<div class="formFieldDesc">
		<p>{$this->getDescription()}</p>
	</div>
</div>
HTML;
	}
}
