<?php
namespace wcf\system\exception;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * AJAXException provides JSON-encoded exceptions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 */
class AJAXException extends LoggedException {
	/**
	 * missing parameters
	 * @var	integer
	 */
	const MISSING_PARAMETERS = 400;
	
	/**
	 * session expired
	 * @var	integer
	 */
	const SESSION_EXPIRED = 401;
	
	/**
	 * insufficient permissions
	 * @var	integer
	 */
	const INSUFFICIENT_PERMISSIONS = 403;
	
	/**
	 * illegal link
	 * @var	integer
	 */
	const ILLEGAL_LINK = 404;
	
	/**
	 * bad parameters
	 * @var	integer
	 */
	const BAD_PARAMETERS = 412;
	
	/**
	 * internal server error
	 * @var	integer
	 */
	const INTERNAL_ERROR = 503;
	
	/**
	 * Throws a JSON-encoded error message
	 * 
	 * @param	string		$message
	 * @param	integer		$errorType
	 * @param	string		$stacktrace
	 * @param	mixed[]		$returnValues
	 * @param	string		$exceptionID
	 */
	public function __construct($message, $errorType = self::INTERNAL_ERROR, $stacktrace = null, $returnValues = [], $exceptionID = '') {
		if ($stacktrace === null) $stacktrace = $this->getTraceAsString();
		
		$responseData = [
			'code' => $errorType,
			'message' => $message,
			'returnValues' => $returnValues
		];
		
		// include a stacktrace if:
		// - debug mode is enabled
		// - within ACP and a SystemException was thrown
		if (WCF::debugModeIsEnabled(false) || WCF::debugModeIsEnabled() && self::INTERNAL_ERROR) {
			$responseData['stacktrace'] = nl2br($stacktrace, false);
		}
		
		$statusHeader = '';
		switch ($errorType) {
			case self::MISSING_PARAMETERS:
				$statusHeader = 'HTTP/1.0 400 Bad Request';
				
				$responseData['exceptionID'] = $exceptionID;
				$responseData['message'] = WCF::getLanguage()->get('wcf.ajax.error.badRequest');
			break;
			
			case self::SESSION_EXPIRED:
				$statusHeader = 'HTTP/1.0 409 Conflict';
			break;
			
			case self::INSUFFICIENT_PERMISSIONS:
				$statusHeader = 'HTTP/1.0 403 Forbidden';
			break;
			
			case self::BAD_PARAMETERS:
				$statusHeader = 'HTTP/1.0 431 Bad Parameters';
				
				$responseData['exceptionID'] = $exceptionID;
			break;
			
			default:
			case self::INTERNAL_ERROR:
				//header('HTTP/1.0 418 I\'m a Teapot');
				header('HTTP/1.0 503 Service Unavailable');
				
				$responseData['code'] = self::INTERNAL_ERROR;
				$responseData['exceptionID'] = $exceptionID;
				if (!WCF::debugModeIsEnabled()) {
					$responseData['message'] = WCF::getLanguage()->get('wcf.ajax.error.internalError');
				}
			break;
		}
		
		header($statusHeader);
		header('Content-type: application/json');
		echo JSON::encode($responseData);
		exit;
	}
}
