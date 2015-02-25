<?php
namespace wcf\form;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a form using captcha.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
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
	public $captchaObjectTypeName = CAPTCHA_TYPE;
	
	/**
	 * true if recaptcha is used
	 * @var	boolean
	 */
	public $useCaptcha = true;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'captchaObjectType' => $this->captchaObjectType,
			'useCaptcha' => $this->useCaptcha
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		if (!WCF::getUser()->userID && $this->useCaptcha && $this->captchaObjectTypeName) {
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
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->reset();
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
	}
}
