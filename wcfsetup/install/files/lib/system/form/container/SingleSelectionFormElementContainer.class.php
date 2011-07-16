<?php
namespace wcf\system\form\container;

/**
 * Provides a single selection form element container.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.form
 * @category 	Community Framework
 */
class SingleSelectionFormElementContainer extends SelectionFormElementContainer {
	/**
	 * container value
	 *
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
	 * @see	wcf\system\form\FormElementContainer::getHTML()
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
