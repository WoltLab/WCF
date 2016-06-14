<?php
namespace wcf\system\exception;

/**
 * This is a custom implementation of the default \ErrorException.
 * It is used for backwards compatibility reasons. Do not rely on it
 * inheriting \wcf\system\exception\SystemException.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @since	3.0
 */
class ErrorException extends SystemException {
	/**
	 * @inheritDoc
	 */
	protected $severity;
	
	/**
	 * @inheritDoc
	 */
	public function __construct($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null) {
		parent::__construct($message, $code, "", $previous);
		
		$this->severity = $severity;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSeverity() {
		return $this->severity;
	}
}
