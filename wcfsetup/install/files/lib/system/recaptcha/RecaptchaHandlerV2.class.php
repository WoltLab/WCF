<?php
namespace wcf\system\recaptcha;
use wcf\system\exception\SystemException;
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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.recaptcha
 * @category	Community Framework
 */
class RecaptchaHandlerV2 extends SingletonFactory {
	/**
	 * Validates response.
	 * 
	 * @param	string		$response
	 */
	public function validate($response) {
		// fail if response is empty to avoid sending api requests
		if (empty($response)) {
			throw new UserInputException('recaptchaString', 'false');
		}
		
		$request = new HTTPRequest('https://www.google.com/recaptcha/api/siteverify?secret='.rawurlencode(RECAPTCHA_PRIVATEKEY).'&response='.rawurlencode($response).'&remoteip='.rawurlencode(UserUtil::getIpAddress()), array('timeout' => 10));
		
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
		catch (SystemException $e) {
			// log error, but accept captcha
			$e->getExceptionID();
		}
		
		WCF::getSession()->register('recaptchaDone', true);
	}
}
