<?php
namespace wcf\system\form\element;
use wcf\util\StringUtil;

/**
 * Provides a checkbox form element.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Element
 */
class MultipleSelectionFormElement extends AbstractNamedFormElement {
	/**
	 * message displayed if the input is disabled
	 * @var	string
	 */
	protected $disabledMessage = '';
	
	/**
	 * @inheritDoc
	 */
	public function setValue($value) {
		if (!is_array($value)) {
			parent::setValue($value);
		}
		else {
			$this->value = array_map([StringUtil::class, 'trim'], $value);
		}
	}
	
	/**
	 * Sets message displayed if input should be disabled.
	 * 
	 * @param	string		$message
	 */
	public function setDisabledMessage($message) {
		$this->disabledMessage = $message;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		if ($this->disabledMessage) {
			return $this->disabledMessage;
		}
		
		return parent::getDescription();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML($formName) {
		$disabled = '';
		if ($this->disabledMessage) {
			$disabled = ' disabled';
		}
		
		return <<<HTML
<label><input type="checkbox" name="{$formName}{$this->getParent()->getName()}[]" value="{$this->getValue()}"{$disabled}> {$this->getLabel()}</label>
<small>{$this->getDescription()}</small>
HTML;
	}
}
