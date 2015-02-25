<?php
namespace wcf\system\form\container;
use wcf\util\StringUtil;

/**
 * Provides a single selection form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category	Community Framework
 */
class SingleSelectionFormElementContainer extends SelectionFormElementContainer {
	/**
	 * container value
	 * @var	string
	 */
	protected $value = '';
	
	/**
	 * Sets container value.
	 * 
	 * @param	string		$value
	 */
	public function setValue($value) {
		$this->value = StringUtil::trim($value);
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getValue()
	 */
	public function getValue($key) {
		return $this->value;
	}
	
	/**
	 * @see	\wcf\system\form\IFormElementContainer::getHTML()
	 */
	public function getHTML($formName) {
		$content = '';
		foreach ($this->getChildren() as $element) {
			$content .= $element->getHTML($formName);
		}
		
		return <<<HTML
<fieldset>
	<legend>{$this->getLabel()}</legend>
	
	<small>{$this->getDescription()}</small>
	
	<dl>
		<dd>{$content}</dd>
	</dl>
</fieldset>
HTML;
	}
}
