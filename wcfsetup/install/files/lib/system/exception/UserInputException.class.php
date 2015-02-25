<?php
namespace wcf\system\exception;

/**
 * UserInputException handles all formular input errors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class UserInputException extends UserException {
	/**
	 * name of error field
	 * @var	string
	 */
	protected $field = null;
	
	/**
	 * error type
	 * @var	string
	 */
	protected $type = null;
	
	/**
	 * variables for AJAX error handling
	 * @var	array
	 */
	protected $variables = array();
	
	/**
	 * Creates a new UserInputException.
	 * 
	 * @param	string		$field		affected formular field
	 * @param	string		$type		kind of this error
	 * @param	array		$variables	additional variables for AJAX error handling
	 */
	public function __construct($field = '', $type = 'empty', array $variables = array()) {
		$this->field = $field;
		$this->type = $type;
		$this->variables = $variables;
		$this->message = 'Parameter '.$field.' is missing or invalid';
		
		parent::__construct();
	}
	
	/**
	 * Returns the affected formular field of this error.
	 * 
	 * @return	string
	 */
	public function getField() {
		return $this->field;
	}
	
	/**
	 * Returns the kind of this error.
	 * 
	 * @return	string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Returns additional variables for AJAX error handling.
	 * 
	 * @return	array
	 */
	public function getVariables() {
		return $this->variables;
	}
}
