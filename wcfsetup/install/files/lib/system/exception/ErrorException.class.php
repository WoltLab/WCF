<?php
namespace wcf\system\exception;

/**
 * This is a custom implementation of the default \ErrorException.
 * It is used for backwards compatibility reasons. Do not rely on it
 * inheriting \wcf\system\exception\SystemException.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 * @since	2.2
 */
class ErrorException extends SystemException {
	/**
	 * @see \ErrorException::$severity
	 */
	protected $severity;
	
	/**
	 * @see \ErrorException::__construct()
	 */
	public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null) {
		parent::__construct($message, $code, "", $previous);
		
		$this->severity = $severity;
	}
	
	/**
	 * @see \ErrorException::getSeverity()
	 */
	public function getSeverity() {
		return $this->severity;
	}
}
