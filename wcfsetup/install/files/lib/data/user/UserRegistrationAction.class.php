<?php
namespace wcf\data\user;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Executes user registration-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user
 * @category	Community Framework
 */
class UserRegistrationAction extends UserAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('validateEmailAddress', 'validatePassword', 'validateUsername');
	
	/**
	 * Validates the validate username function.
	 */
	public function validateValidateUsername() {
		$this->readString('username');
	}
	
	/**
	 * Validates the validate email address function.
	 */
	public function validateValidateEmailAddress() {
		$this->readString('email');
	}
	
	/**
	 * Validates the validate password function.
	 */
	public function validateValidatePassword() {
		$this->readString('password');
	}
	
	/**
	 * Validates the given username.
	 * 
	 * @return	array
	 */
	public function validateUsername() {
		if (!UserRegistrationUtil::isValidUsername($this->parameters['username'])) {
			return array(
				'isValid' => false,
				'error' => 'notValid'
			);
		}
		
		if (!UserUtil::isAvailableUsername($this->parameters['username'])) {
			return array(
				'isValid' => false,
				'error' => 'notUnique'
			);
		}
		
		return array(
			'isValid' => true
		);
	}
	
	/**
	 * Validates given email address.
	 * 
	 * @return	array
	 */
	public function validateEmailAddress() {
		if (!UserRegistrationUtil::isValidEmail($this->parameters['email'])) {
			return array(
				'isValid' => false,
				'error' => 'notValid'
			);
		}
		
		if (!UserUtil::isAvailableEmail($this->parameters['email'])) {
			return array(
				'isValid' => false,
				'error' => 'notUnique'
			);
		}
		
		return array(
			'isValid' => true
		);
	}
	
	/**
	 * Validates given password.
	 * 
	 * @return	array
	 */
	public function validatePassword() {
		if (!UserRegistrationUtil::isSecurePassword($this->parameters['password'])) {
			return array(
				'isValid' => false,
				'error' => 'notSecure'
			);
		}
		
		return array(
			'isValid' => true
		);
	}
}
