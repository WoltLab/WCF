<?php
namespace wcf\data\user;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Executes user registration-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 */
class UserRegistrationAction extends UserAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['validateEmailAddress', 'validatePassword', 'validateUsername'];
	
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
			return [
				'isValid' => false,
				'error' => 'notValid'
			];
		}
		
		if (!UserUtil::isAvailableUsername($this->parameters['username'])) {
			return [
				'isValid' => false,
				'error' => 'notUnique'
			];
		}
		
		return [
			'isValid' => true
		];
	}
	
	/**
	 * Validates given email address.
	 * 
	 * @return	array
	 */
	public function validateEmailAddress() {
		if (!UserRegistrationUtil::isValidEmail($this->parameters['email'])) {
			return [
				'isValid' => false,
				'error' => 'notValid'
			];
		}
		
		if (!UserUtil::isAvailableEmail($this->parameters['email'])) {
			return [
				'isValid' => false,
				'error' => 'notUnique'
			];
		}
		
		return [
			'isValid' => true
		];
	}
	
	/**
	 * Validates given password.
	 * 
	 * @return	array
	 */
	public function validatePassword() {
		if (!UserRegistrationUtil::isSecurePassword($this->parameters['password'])) {
			return [
				'isValid' => false,
				'error' => 'notSecure'
			];
		}
		
		return [
			'isValid' => true
		];
	}
}
