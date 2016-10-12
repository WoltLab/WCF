<?php
namespace wcf\system\captcha;
use wcf\system\recaptcha\RecaptchaHandlerV2;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Captcha handler for reCAPTCHA.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Captcha
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
	 * @inheritDoc
	 */
	public function getFormElement() {
		if (WCF::getSession()->getVar('recaptchaDone')) return '';
		
		if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
			// V1
			\wcf\system\recaptcha\RecaptchaHandler::getInstance()->assignVariables();
		}
		else {
			// V2
			WCF::getTPL()->assign([
				'recaptchaLegacyMode' => true
			]);
		}
		
		return WCF::getTPL()->fetch('recaptcha');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return true;
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function reset() {
		WCF::getSession()->unregister('recaptchaDone');
	}
	
	/**
	 * @inheritDoc
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
