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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.recaptcha
 * @category	Community Framework
 */
class RecaptchaHandler extends SingletonFactory {
	/**
	 * list of supported languages
	 * @var	array<string>
	 * @see	http://code.google.com/intl/de-DE/apis/recaptcha/docs/customization.html#i18n
	 */
	protected $supportedLanguages = array(
		'de', 'en', 'es', 'fr', 'nl', 'pt', 'ru', 'tr'
	);
	
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
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// set appropriate language code, fallback to EN if language code is not known to reCAPTCHA-API
		$this->languageCode = WCF::getLanguage()->getFixedLanguageCode();
		if (!in_array($this->languageCode, $this->supportedLanguages)) {
			$this->languageCode = 'en';
		}
		
		// fetch appropriate keys
		$this->publicKey = $this->getKey(RECAPTCHA_PUBLICKEY, 'public');
		$this->privateKey = $this->getKey(RECAPTCHA_PRIVATEKEY, 'private');
	}
	
	/**
	 * Returns appropriate public or private key, supports multiple hosts.
	 * 
	 * @param	string		$pubKey
	 * @param	string		$type
	 * @return	string
	 */
	protected function getKey($pubKey, $type) {
		// check if multiple keys are given
		$keys = explode("\n", $pubKey);
		if (count($keys) > 1) {
			foreach ($keys as $key) {
				$keyParts = explode(':', $key);
				
				if (StringUtil::trim($keyParts[0]) == $_SERVER['HTTP_HOST']) {
					return StringUtil::trim($keyParts[1]);
				}
			}
		}
		else {
			return $pubKey;
		}
		
		throw new SystemException('No valid '.$type.' key for reCAPTCHA found.');
	}
	
	/**
	 * Validates response against given challenge.
	 * 
	 * @param	string		$challenge
	 * @param	string		$response
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
	 */
	protected function verify($challenge, $response) {
		$request = new HTTPRequest('http://www.google.com/recaptcha/api/verify', array(), array(
			'privatekey' => $this->privateKey,
			'remoteip' => UserUtil::getIpAddress(),
			'challenge' => $challenge,
			'response' => $response
		));
		
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
		WCF::getTPL()->assign(array(
			'recaptchaLanguageCode' => $this->languageCode,
			'recaptchaPublicKey' => $this->publicKey,
			'recaptchaUseSSL' => RouteHandler::secureConnection()
		));
	}
}
