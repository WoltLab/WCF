<?php
namespace wcf\util\exception;
use wcf\system\exception\IExtraInformationException;
use wcf\system\exception\SystemException;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;

/**
 * Denotes failure to perform a HTTP request.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util\Exception
 * @since	3.0
 */
class HTTPException extends SystemException implements IExtraInformationException {
	/**
	 * The HTTP request that lead to this Exception.
	 * 
	 * @param	HTTPRequest
	 */
	protected $http = null;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(HTTPRequest $http, $message, $code = 0, $previous = null) {
		parent::__construct($message, $code, '', $previous);
		
		$this->http = $http;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getExtraInformation() {
		$reply = $this->http->getReply();
		$body = StringUtil::truncate(preg_replace('/[\x00-\x1F\x80-\xFF]/', '.', $reply['body']), 512, StringUtil::HELLIP, true);
		
		return [
			['Body', $body],
			['Status Code', $reply['statusCode']]
		];
	}
}
