<?php
namespace wcf\system\exception;
use wcf\util\JSON;

/**
 * AJAXException provides JSON-encoded exceptions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category 	Community Framework
 */
class AJAXException extends \Exception {
	/**
	 * Throws a JSON-encoded error message
	 * 
	 * @param	string		$message
	 * @param	string		$stacktrace
	 */
	public function __construct($message, $stacktrace = null) {
		if ($stacktrace === null) $stacktrace = $this->getTraceAsString();
		
		//header('HTTP/1.0 418 I\'m a Teapot');
		header('HTTP/1.0 503 Service Unavailable');
		header('Content-type: application/json');
		echo JSON::encode(array(
			'message' => $message,
			'stacktrace' => nl2br($stacktrace)
		));
		exit;
	}
}
