<?php
namespace wcf\system\captcha;
use wcf\system\recaptcha\RecaptchaHandlerV2;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Captcha handler for reCAPTCHA.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.captcha
 * @category	Community Framework
 */
class RecaptchaHandler implements ICaptchaHandler {
	/**
	 * recaptcha challenge
	 * @var	string
	 */
	public $challenge = '';
	
	/**
	 * response to the challenge
	 * @var	string
	 */
	public $response = '';
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::getFormElement()
	 */
	public function getFormElement() {
		if (WCF::getSession()->getVar('recaptchaDone')) return '';
		
		if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
			// V1
			\wcf\system\recaptcha\RecaptchaHandler::getInstance()->assignVariables();
		}
		else {
			// V2
			WCF::getTPL()->assign(array(
				'recaptchaLegacyMode' => true
			));
		}
		
		return WCF::getTPL()->fetch('recaptcha');
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::isAvailable()
	 */
	public function isAvailable() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::readFormParameters()
	 */
	public function readFormParameters() {
		if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
			// V1
			if (isset($_POST['recaptcha_challenge_field'])) $this->challenge = StringUtil::trim($_POST['recaptcha_challenge_field']);
			if (isset($_POST['recaptcha_response_field'])) $this->response = StringUtil::trim($_POST['recaptcha_response_field']);
		}
		else {
			// V2
			if (isset($_POST['g-recaptcha-response'])) $this->response = $_POST['g-recaptcha-response'];
		}
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::reset()
	 */
	public function reset() {
		WCF::getSession()->unregister('recaptchaDone');
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::validate()
	 */
	public function validate() {
		if (WCF::getSession()->getVar('recaptchaDone')) return;
		
		if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
			// V1
			\wcf\system\recaptcha\RecaptchaHandler::getInstance()->validate($this->challenge, $this->response);
		}
		else {
			// V2
			RecaptchaHandlerV2::getInstance()->validate($this->response);
		}
	}
}
