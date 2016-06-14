<?php
namespace wcf\form;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a form using captcha.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
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
	 * true if captcha is used
	 * @var	boolean
	 */
	public $useCaptcha = true;
	
	/**
	 * true to force captcha usage
	 * @var	boolean
	 */
	public $forceCaptcha = false;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'captchaObjectType' => $this->captchaObjectType,
			'useCaptcha' => $this->useCaptcha
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if ((!WCF::getUser()->userID || $this->forceCaptcha) && $this->useCaptcha && $this->captchaObjectTypeName) {
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->readFormParameters();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->reset();
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
		if ($this->captchaObjectType) {
			$this->captchaObjectType->getProcessor()->validate();
		}
	}
}
