<?php
namespace wcf\form;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\exception\SystemException;
use wcf\system\recaptcha\RecaptchaHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a form using captcha.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractCaptchaForm extends AbstractForm {
	/**
	 * captcha object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $captchaObjectType = null;
	
	/**
	 * name of the captcha object type; if empty, captcha is disabled
	 * @var	string
	 */
	public $captchaObjectTypeName = '';
	
	/**
	 * challenge (legacy property from RecaptchaForm, do not use!)
	 * @var	string
	 */
	public $challenge = '';
	
	/**
	 * response (legacy property from RecaptchaForm, do not use!)
	 * @var	string
	 */
	public $response = '';
	
	/**
	 * true if recaptcha is used (legacy property from RecaptchaForm, do not use!)
	 * @var	boolean
	 */
	public $useCaptcha = true;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'captchaObjectType' => $this->captchaObjectType
		));
		
		if (!$this->captchaObjectType) {
			RecaptchaHandler::getInstance()->assignVariables();
			WCF::getTPL()->assign(array(
				'useCaptcha' => $this->useCaptcha
			));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		if (!WCF::getUser()->userID && $this->captchaObjectTypeName) {
			$this->captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName($this->captchaObjectTypeName);
			if ($this->captchaObjectType === null) {
				throw new SystemException("Unknown captcha object type with name '".$this->captchaObjectTypeName."'");
			}
			
			if (!$this->captchaObjectType->getProcessor()->isAvailable()) {
				$this->captchaObjectType = null;
			}
		}
		
		parent::readData();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->readFormParameters();
		}
		else if ($this->useCaptcha) {
			if (isset($_POST['recaptcha_challenge_field'])) $this->challenge = StringUtil::trim($_POST['recaptcha_challenge_field']);
			if (isset($_POST['recaptcha_response_field'])) $this->response = StringUtil::trim($_POST['recaptcha_response_field']);
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if ($this->captchaObjectType === null && (!MODULE_SYSTEM_RECAPTCHA || WCF::getUser()->userID || WCF::getSession()->getVar('recaptchaDone'))) {
			$this->useCaptcha = false;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->reset();
		}
		else {
			WCF::getSession()->unregister('recaptchaDone');
		}
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
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->validate();
		}
		else if ($this->useCaptcha) {
			RecaptchaHandler::getInstance()->validate($this->challenge, $this->response);
			$this->useCaptcha = false;
		}
	}
}
