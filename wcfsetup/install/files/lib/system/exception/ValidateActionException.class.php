<?php
namespace wcf\system\exception;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Simple exception for AJAX-driven requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
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
	 * @see	\Exception::__construct()
	 */
	public function __construct($fieldName, $errorMessage = 'empty', array $variables = array()) {
		$this->errorMessage = $errorMessage;
		if (StringUtil::indexOf($this->errorMessage, '.') === false) {
			$this->errorMessage = WCF::getLanguage()->get('wcf.global.form.error.'.$this->errorMessage);
		}
		else {
			$this->errorMessage = WCF::getLanguage()->getDynamicVariable($this->errorMessage, $variables);
		}
		
		$this->fieldName = $fieldName;
		$this->message = WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.invalidParameter', array('fieldName' => $this->fieldName));
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
	 * @see	\Exception::__toString()
	 */
	public function __toString() {
		return $this->message;
	}
}
