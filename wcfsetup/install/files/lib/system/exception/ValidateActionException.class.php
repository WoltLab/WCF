<?php
namespace wcf\system\exception;

/**
 * Simple exception for AJAX-driven requests.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 */
class ValidateActionException extends \Exception {
	/**
	 * @see	\Exception::__construct()
	 */
	public function __construct($message) {
		$this->message = $message;
	}
	
	/**
	 * @see	\Exception::__toString()
	 */
	public function __toString() {
		return $this->message;
	}
}
