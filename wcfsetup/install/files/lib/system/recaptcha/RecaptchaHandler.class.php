<?php
namespace wcf\system\recaptcha;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RouteHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\HTTPRequest;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Handles reCAPTCHA support.
 * 
 * Based upon reCAPTCHA-plugin originally created in 2010 by Markus Bartz <roul@codingcorner.info>
 * and released under the conditions of the GNU Lesser General Public License.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Recaptcha
 */
class RecaptchaHandler extends SingletonFactory {
	/**
	 * list of supported languages
	 * @var	string[]
	 * @see	http://code.google.com/intl/de-DE/apis/recaptcha/docs/customization.html#i18n
	 */
	protected $supportedLanguages = ['de', 'en', 'es', 'fr', 'nl', 'pt', 'ru', 'tr'];
	
	/**
	 * language code
	 * @var	string
	 */
	protected $languageCode = '';
	
	/**
	 * public key
	 * @var	string
	 */
	protected $publicKey = '';
	
	/**
	 * private key
	 * @var	string
	 */
	protected $privateKey = '';
	
	// reply codes (see <http://code.google.com/intl/de-DE/apis/recaptcha/docs/verify.html>)
	const VALID_ANSWER = 'valid';
	const ERROR_UNKNOWN = 'unknown';
	const ERROR_INVALID_PUBLICKEY = 'invalid-site-public-key';
	const ERROR_INVALID_PRIVATEKEY = 'invalid-site-private-key';
	const ERROR_INVALID_COOKIE = 'invalid-request-cookie';
	const ERROR_INCORRECT_SOLUTION = 'incorrect-captcha-sol';
	const ERROR_INCORRECT_PARAMS = 'verify-params-incorrect';
	const ERROR_INVALID_REFFERER = 'invalid-referrer';
	const ERROR_NOT_REACHABLE = 'recaptcha-not-reachable';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// set appropriate language code, fallback to EN if language code is not known to reCAPTCHA-API
		$this->languageCode = WCF::getLanguage()->getFixedLanguageCode();
		if (!in_array($this->languageCode, $this->supportedLanguages)) {
			$this->languageCode = 'en';
		}
		
		// WoltLab's V1 OEM keys
		$this->publicKey = '6LfOlMYSAAAAADvo3s4puBAYDqI-6YK2ybe7BJE5';
		$this->privateKey = '6LfOlMYSAAAAAKR3m_EFxmDv1xS8PCfeaSZ2LdG9';
	}
	
	/**
	 * Validates response against given challenge.
	 * 
	 * @param	string		$challenge
	 * @param	string		$response
	 * @throws	SystemException
	 * @throws	UserInputException
	 */
	public function validate($challenge, $response) {
		// fail if challenge or response are empty to avoid sending api requests
		if (empty($challenge) || empty($response)) {
			throw new UserInputException('recaptchaString', 'false');
		}
		
		$response = $this->verify($challenge, $response);
		switch ($response) {
			case self::VALID_ANSWER:
				break;
			
			case self::ERROR_INCORRECT_SOLUTION:
				throw new UserInputException('recaptchaString', 'false');
				break;
			
			case self::ERROR_NOT_REACHABLE:
				// if reCaptcha server is unreachable mark captcha as done
				// this should be better than block users until server is back.
				// - RouL
				break;
				
			case self::ERROR_INVALID_COOKIE:
				// do not throw a system exception, if validation fails
				// while javascript is disabled. Otherwise, bots may produce
				// a lot of log entries.
				throw new UserInputException('recaptchaString', 'false');
				break;
			
			default:
				throw new SystemException('reCAPTCHA returned the following error: '.$response);
		}
		
		WCF::getSession()->register('recaptchaDone', true);
	}
	
	/**
	 * Queries server to verify successful response.
	 * 
	 * @param	string		$challenge
	 * @param	string		$response
	 * @return	string
	 */
	protected function verify($challenge, $response) {
		$request = new HTTPRequest('http://www.google.com/recaptcha/api/verify', ['timeout' => 10], [
			'privatekey' => $this->privateKey,
			'remoteip' => UserUtil::getIpAddress(),
			'challenge' => $challenge,
			'response' => $response
		]);
		
		try {
			$request->execute();
			$reply = $request->getReply();
			$reCaptchaResponse = explode("\n", $reply['body']);
			
			if (StringUtil::trim($reCaptchaResponse[0]) === "true") {
				return self::VALID_ANSWER;
			}
			else {
				return StringUtil::trim($reCaptchaResponse[1]);
			}
		}
		catch (SystemException $e) {
			return self::ERROR_NOT_REACHABLE;
		}
	}
	
	/**
	 * Assigns template variables for reCAPTCHA.
	 */
	public function assignVariables() {
		WCF::getTPL()->assign([
			'recaptchaLanguageCode' => $this->languageCode,
			'recaptchaPublicKey' => $this->publicKey,
			'recaptchaUseSSL' => RouteHandler::secureConnection(), // @deprecated 2.1
			'recaptchaLegacyMode' => true
		]);
	}
}
