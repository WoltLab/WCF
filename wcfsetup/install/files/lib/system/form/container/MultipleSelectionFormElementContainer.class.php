<?php
namespace wcf\system\form\container;

/**
 * Provides a multiple selection form element container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category	Community Framework
 */
class MultipleSelectionFormElementContainer extends SelectionFormElementContainer {
	/**
	 * container value
	 * @var	array
	 */
	protected $value = array();
	
	/**
	 * Sets container value.
	 * 
	 * @param	array		$value
	 */
	public function setValue(array $value) {
		$this->value = $value;
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
		<small>{$this->getDescription()}</small>
	</dd>
</dl>
HTML;
	}
}
