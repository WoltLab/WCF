<?php
namespace wcf\system\form\container;

/**
 * Provides a multiple selection form element container.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form.container
 * @category 	Community Framework
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
	 * Returns container value.
	 *
	 * @return	array
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
<div class="formGroup">
	<div class="formGroupLabel">
		<label>{$this->getLabel()}</label>
	</div>
	<div class="formGroupField">
		<fieldset>
			<legend>{$this->getLabel()}</legend>
			
			<div class="formField">
				{$content}
			</div>
		</fieldset>
	</div>
	<div class="formGroupFieldDesc">
		<p>{$this->getDescription()}</p>
	</div>
</div>
HTML;
	}
}
