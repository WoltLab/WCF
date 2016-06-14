<?php
namespace wcf\system\recaptcha;
use wcf\system\exception\UserInputException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\util\UserUtil;

/**
 * Handles reCAPTCHA V2 support.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Recaptcha
 */
class RecaptchaHandlerV2 extends SingletonFactory {
	/**
	 * Validates response.
	 * 
	 * @param	string		$response
	 * @throws	UserInputException
	 */
	public function validate($response) {
		// fail if response is empty to avoid sending api requests
		if (empty($response)) {
			throw new UserInputException('recaptchaString', 'false');
		}
		
		$request = new HTTPRequest('https://www.google.com/recaptcha/api/siteverify?secret='.rawurlencode(RECAPTCHA_PRIVATEKEY).'&response='.rawurlencode($response).'&remoteip='.rawurlencode(UserUtil::getIpAddress()), ['timeout' => 10]);
		
		try {
			$request->execute();
			$reply = $request->getReply();
			$data = JSON::decode($reply['body']);
			
			if ($data['success']) {
				// yeah
			}
			else {
				throw new UserInputException('recaptchaString', 'false');
			}
		}
		catch (\Exception $e) {
			// log error, but accept captcha
			\wcf\functions\exception\logThrowable($e);
		}
		
		WCF::getSession()->register('recaptchaDone', true);
	}
}
