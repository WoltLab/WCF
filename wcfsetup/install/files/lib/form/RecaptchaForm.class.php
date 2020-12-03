<?php
namespace wcf\form;
use wcf\system\recaptcha\RecaptchaHandlerV2;
use wcf\system\WCF;

/**
 * RecaptchaForm is an abstract form implementation for the use of reCAPTCHA.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
		
		if (isset($_POST['g-recaptcha-response'])) $this->response = $_POST['g-recaptcha-response'];
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
			RecaptchaHandlerV2::getInstance()->validate($this->response);
			
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
		
		WCF::getTPL()->assign([
			'recaptchaLegacyMode' => true
		]);
		
		WCF::getTPL()->assign([
			'useCaptcha' => $this->useCaptcha
		]);
	}
}
