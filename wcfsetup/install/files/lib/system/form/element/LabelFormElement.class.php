<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Provides a label form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Element
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
	 * @inheritDoc
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
