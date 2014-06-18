<?php
namespace wcf\system\captcha;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Captcha handler for reCAPTCHA.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
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
		\wcf\system\recaptcha\RecaptchaHandler::getInstance()->assignVariables();
		
		return WCF::getTPL()->fetch('recaptcha');
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::isAvailable()
	 */
	public function isAvailable() {
		return MODULE_SYSTEM_RECAPTCHA && RECAPTCHA_PUBLICKEY && RECAPTCHA_PRIVATEKEY;
	}
	
	/**
	 * @see	\wcf\system\captcha\ICaptchaHandler::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['recaptcha_challenge_field'])) $this->challenge = StringUtil::trim($_POST['recaptcha_challenge_field']);
		if (isset($_POST['recaptcha_response_field'])) $this->response = StringUtil::trim($_POST['recaptcha_response_field']);
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
		\wcf\system\recaptcha\RecaptchaHandler::getInstance()->validate($this->challenge, $this->response);
	}
}
