<?php
namespace wcf\system\form\container;
use wcf\util\StringUtil;

/**
 * Provides a single selection form element container.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category 	Community Framework
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
	 * Returns container value.
	 *
	 * @return	string
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @see	wcf\system\form\IFormElementContainer::getHTML()
	 */
	public function getHTML($formName) {
		$content = '';
		foreach ($this->getChildren() as $element) {
			$content .= $element->getHTML($formName);
		}
		
		return <<<HTML
<dl>
	<dt><label>{$this->getLabel()}</label></dt>
	<dd>
		<fieldset>
			<legend>{$this->getLabel()}</legend>
			
			<div>
				{$content}
			</div>
		</fieldset>
	</dd>
	<small>{$this->getDescription()}</small>
	</div>
</dl>
HTML;
	}
}
