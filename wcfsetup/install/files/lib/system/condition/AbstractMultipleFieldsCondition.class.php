<?php
namespace wcf\system\condition;
use wcf\system\WCF;

/**
 * Abstract implementation of a condition for multiple fields.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
abstract class AbstractMultipleFieldsCondition extends AbstractCondition {
	/**
	 * language items of the input element descriptions
	 * @var	string[]
	 */
	protected $descriptions = [];
	
	/**
	 * error messages if the validation failed
	 * @var	string[]
	 */
	protected $errorMessages = [];
	
	/**
	 * language items of the input element labels
	 * @var	string[]
	 */
	protected $labels = [];
	
	/**
	 * Returns the description element for the HTML output.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	protected function getDescriptionElement($identifier) {
		if (isset($this->descriptions[$identifier])) {
			return '<small>'.WCF::getLanguage()->get($this->descriptions[$identifier]).'</small>';
		}
		
		return '';
	}
	
	/**
	 * Returns the error class for the definition list element.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	public function getErrorClass($identifier) {
		if (isset($this->errorMessages[$identifier])) {
			return ' class="formError"';
		}
		
		return '';
	}
	
	/**
	 * Returns the error message element for the HTML output.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	protected function getErrorMessageElement($identifier) {
		if (isset($this->errorMessages[$identifier])) {
			return '<small class="innerError">'.WCF::getLanguage()->get($this->errorMessages[$identifier]).'</small>';
		}
		
		return '';
	}
	
	/**
	 * Returns the label of the input element.
	 * 
	 * @param	string		$identifier
	 * @return	string
	 */
	protected function getLabel($identifier) {
		if (isset($this->labels[$identifier])) {
			return '<label for="'.$identifier.'">'.WCF::getLanguage()->get($this->labels[$identifier]).'</label>';
		}
		
		return '';
	}
}
