<?php
namespace wcf\system\exception;

/**
 * UserInputException handles all formular input errors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class UserInputException extends UserException {
	/**
	 * name of error field
	 * @var string
	 */
	protected $field = null;
	
	/**
	 * error type
	 * @var	string
	 */
	protected $type = null;
	
	/**
	 * Creates a new UserInputException.
	 * 
	 * @param	string		$field		affected formular field
	 * @param	string		$type		kind of this error
	 */
	public function __construct($field = '', $type = 'empty') {
		$this->field = $field;
		$this->type = $type;
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
}
