<?php
namespace wcf\system\condition;
use wcf\system\WCF;

/**
 * Abstract implementation of a condition for a single field.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
abstract class AbstractSingleFieldCondition extends AbstractCondition implements ICondition {
	/**
	 * language item of the input element description
	 * @var	string
	 */
	protected $description = '';
	
	/**
	 * error message if the validation failed
	 * @var	string
	 */
	protected $errorMessage = '';
	
	/**
	 * language item of the input element label
	 * @var	string
	 */
	protected $label = '';
	
	/**
	 * Returns the description element for the HTML output.
	 * 
	 * @return	string
	 */
	protected function getDescriptionElement() {
		if ($this->description) {
			return '<small>'.WCF::getLanguage()->get($this->description).'</small>';
		}
		
		return '';
	}
	
	/**
	 * Returns the error class for the definition list element.
	 * 
	 * @return	string
	 */
	public function getErrorClass() {
		if ($this->errorMessage) {
			return ' class="formError"';
		}
		
		return '';
	}
	
	/**
	 * Returns the error message element for the HTML output.
	 * 
	 * @return	string
	 */
	protected function getErrorMessageElement() {
		if ($this->errorMessage) {
			return '<small class="innerError">'.WCF::getLanguage()->get($this->errorMessage).'</small>';
		}
		
		return '';
	}
	
	/**
	 * Returns the output of the field element.
	 * 
	 * @return	string
	 */
	abstract protected function getFieldElement();
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		return <<<HTML
<dl>
	<dt>{$this->getLabel()}</dt>
	<dd>
		{$this->getFieldElement()}
		{$this->getDescriptionElement()}
		{$this->getErrorMessageElement()}
	</dd>
</dl>
HTML;
	}
	
	/**
	 * Returns the label of the input element.
	 * 
	 * @return	string
	 */
	protected function getLabel() {
		return WCF::getLanguage()->get($this->label);
	}
}
