<?php
namespace wcf\system\exception;

/**
 * A SystemException is thrown when an unexpected error occurs.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class SystemException extends LoggedException {
	/**
	 * error description
	 * @var	string
	 */
	protected $description = null;
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $information = '';
	
	/**
	 * additional information
	 * @var	string
	 */
	protected $functions = '';
	
	/**
	 * Creates a new SystemException.
	 * 
	 * @param	string		$message	error message
	 * @param	integer		$code		error code
	 * @param	string		$description	description of the error
	 * @param	\Exception	$previous	repacked Exception
	 */
	public function __construct($message = '', $code = 0, $description = '', \Exception $previous = null) {
		parent::__construct((string) $message, (int) $code, $previous);
		$this->description = $description;
	}
	
	/**
	 * Returns the description of this exception.
	 * 
	 * @return	string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() { }
}
