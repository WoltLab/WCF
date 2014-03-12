<?php
namespace wcf\form;
use wcf\system\recaptcha\RecaptchaHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * RecaptchaForm is an abstract form implementation for the use of reCAPTCHA.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
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
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!MODULE_SYSTEM_RECAPTCHA || WCF::getUser()->userID || WCF::getSession()->getVar('recaptchaDone')) {
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['recaptcha_challenge_field'])) $this->challenge = StringUtil::trim($_POST['recaptcha_challenge_field']);
		if (isset($_POST['recaptcha_response_field'])) $this->response = StringUtil::trim($_POST['recaptcha_response_field']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
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
			RecaptchaHandler::getInstance()->validate($this->challenge, $this->response);
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		WCF::getSession()->unregister('recaptchaDone');
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		RecaptchaHandler::getInstance()->assignVariables();
		WCF::getTPL()->assign(array(
			'useCaptcha' => $this->useCaptcha
		));
	}
}
