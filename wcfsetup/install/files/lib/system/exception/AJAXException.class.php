<?php
namespace wcf\system\exception;
use wcf\system\WCF;
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
class AJAXException extends LoggedException {
	/**
	 * Throws a JSON-encoded error message
	 * 
	 * @param	string		$message
	 * @param	string		$stacktrace
	 */
	public function __construct($message, $stacktrace = null) {
		if ($stacktrace === null) $stacktrace = $this->getTraceAsString();
		
		if (WCF::debugModeIsEnabled()) {
			$responseData = array(
				'message' => $message,
				'stacktrace' => nl2br($stacktrace)
			);
		}
		else {
			$responseData = array(
				'message' => $this->_getMessage()
			);
		}
		
		// log error
		$this->logError();
		
		//header('HTTP/1.0 418 I\'m a Teapot');
		header('HTTP/1.0 503 Service Unavailable');
		header('Content-type: application/json');
		echo JSON::encode($responseData);
		exit;
	}
}
