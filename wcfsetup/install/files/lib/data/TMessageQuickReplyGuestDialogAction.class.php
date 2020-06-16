<?php
namespace wcf\data;
use wcf\data\object\type\ObjectType;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\captcha\ICaptchaHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Provides methods related to the guest dialog of message quick reply.
 * 
 * @author	Matthias Schmudt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 */
trait TMessageQuickReplyGuestDialogAction {
	/**
	 * captcha object type used for guests or `null` if no captcha is used or available
	 * @var	ObjectType
	 */
	public $guestDialogCaptchaObjectType;
	
	/**
	 * list of errors in the guest dialog with field as key and error type as value
	 * @var	string[]
	 */
	public $guestDialogErrors = [];
	
	/**
	 * @see	AbstractDatabaseObjectAction::$parameters
	 */
	//protected $parameters = [];
	
	/**
	 * Reads a string value and validates it.
	 *
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 * @see	AbstractDatabaseObjectAction::readString()
	 */
	abstract protected function readString($variableName, $allowEmpty = false, $arrayIndex = '');
	
	/**
	 * Sets the guest dialog captcha.
	 * 
	 * Needs to be called before all other methods in this trait.
	 */
	protected function setGuestDialogCaptcha() {
		if (CAPTCHA_TYPE) {
			$this->guestDialogCaptchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName(CAPTCHA_TYPE);
			if ($this->guestDialogCaptchaObjectType === null) {
				throw new \LogicException("Unknown captcha object type with name '" . CAPTCHA_TYPE . "'");
			}
			
			/** @var ICaptchaHandler $processor */
			$processor = $this->guestDialogCaptchaObjectType->getProcessor();
			
			if (!$processor->isAvailable()) {
				$this->guestDialogCaptchaObjectType = null;
			}
		}
	}
	
	/**
	 * Validates the captcha in the guest dialog.
	 *
	 * @throws	\BadMethodCallException
	 * @throws	\LogicException
	 */
	protected function validateGuestDialogCaptcha() {
		// only relevant for guests
		if (WCF::getUser()->userID) {
			throw new \BadMethodCallException("Guest dialogs are only relevant for guests");
		}
		
		if (CAPTCHA_TYPE && $this->guestDialogCaptchaObjectType) {
			/** @var ICaptchaHandler $processor */
			$processor = $this->guestDialogCaptchaObjectType->getProcessor();
			
			if ($processor->isAvailable()) {
				try {
					$processor->readFormParameters();
					$processor->validate();
				}
				catch (UserInputException $e) {
					$this->guestDialogErrors[$e->getField()] = $e->getType();
				}
			}
		}
	}
	
	/**
	 * Validates the entered username in the guest dialog.
	 * 
	 * @return	string		type of the validation error or empty if no error occurred
	 * @throws	\BadMethodCallException
	 */
	protected function validateGuestDialogUsername() {
		// only relevant for guests
		if (WCF::getUser()->userID) {
			throw new \BadMethodCallException("Guest dialogs are only relevant for guests");
		}
		
		try {
			$this->readString('username', false, 'data');
			
			if (!UserRegistrationUtil::isValidUsername($this->parameters['data']['username'])) {
				throw new UserInputException('username', 'invalid');
			}
			if (!UserUtil::isAvailableUsername($this->parameters['data']['username'])) {
				throw new UserInputException('username', 'notUnique');
			}
		}
		catch (UserInputException $e) {
			$this->guestDialogErrors['username'] = $e->getType();
		}
	}
}
