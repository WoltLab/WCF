<?php
namespace wcf\system\exception;
use wcf\system\WCF;

/**
 * Simple exception for AJAX-driven requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class ValidateActionException extends \Exception {
	/**
	 * error message
	 * @var	string
	 */
	protected $errorMessage = '';
	
	/**
	 * erroneous field name
	 * @var	string
	 */
	protected $fieldName = '';
	
	/**
	 * @inheritDoc
	 */
	public function __construct($fieldName, $errorMessage = 'empty', array $variables = []) {
		$this->errorMessage = $errorMessage;
		if (mb_strpos($this->errorMessage, '.') === false) {
			$this->errorMessage = WCF::getLanguage()->get('wcf.global.form.error.'.$this->errorMessage);
		}
		else {
			$this->errorMessage = WCF::getLanguage()->getDynamicVariable($this->errorMessage, $variables);
		}
		
		$this->fieldName = $fieldName;
		$this->message = WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.invalidParameter', ['fieldName' => $this->fieldName]);
	}
	
	/**
	 * Returns error message.
	 * 
	 * @return	string
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}
	
	/**
	 * Returns erroneous field name.
	 * 
	 * @return	string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->message;
	}
}
