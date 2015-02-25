<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Provides a label form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.element
 * @category	Community Framework
 */
class LabelFormElement extends AbstractFormElement {
	/**
	 * element text
	 * @var	string
	 */
	protected $text = '';
	
	/**
	 * Sets element text.
	 * 
	 * @param	string		$text
	 */
	public function setText($text) {
		$this->text = StringUtil::trim($text);
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
	 * @see	\wcf\system\form\IFormElement::getHTML()
	 */
	public function getHTML($formName) {
		return <<<HTML
<dl>
	<dt><label>{$this->getLabel()}</label></dt>
	<dd>
		{$this->getText()}
		<small>{$this->getDescription()}</small>
	</dd>
</dl>
HTML;
	}
}
