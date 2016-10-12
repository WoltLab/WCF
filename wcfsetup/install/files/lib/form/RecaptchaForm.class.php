<?php
namespace wcf\form;
use wcf\system\recaptcha\RecaptchaHandler;
use wcf\system\recaptcha\RecaptchaHandlerV2;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * RecaptchaForm is an abstract form implementation for the use of reCAPTCHA.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @deprecated	2.1
 */
abstract class RecaptchaForm extends AbstractForm {
	/**
	 * challenge
	 * @var	string
	 */
	public $challenge = '';
	
	/**
	 * response
	 * @var	string
	 */
	public $response = '';
	
	/**
	 * enable recaptcha
	 * @var	boolean
	 */
	public $useCaptcha = true;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->userID || WCF::getSession()->getVar('recaptchaDone')) {
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
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
	public function validate() {
		parent::validate();
		
		$this->validateCaptcha();
	}
	
	/**
	 * Validates the captcha.
	 */
	protected function validateCaptcha() {
		if ($this->useCaptcha) {
			if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
				// V1
				RecaptchaHandler::getInstance()->validate($this->challenge, $this->response);
			}
			else {
				// V2
				RecaptchaHandlerV2::getInstance()->validate($this->response);
			}
			
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		WCF::getSession()->unregister('recaptchaDone');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		if (!RECAPTCHA_PUBLICKEY || !RECAPTCHA_PRIVATEKEY) {
			// V1
			RecaptchaHandler::getInstance()->assignVariables();
		}
		else {
			// V2
			WCF::getTPL()->assign([
				'recaptchaLegacyMode' => true
			]);
		}
		
		WCF::getTPL()->assign([
			'useCaptcha' => $this->useCaptcha
		]);
	}
}
