<?php
namespace wcf\data\user;
use wcf\util\UserRegistrationUtil;
use wcf\util\UserUtil;

/**
 * Executes user registration-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
				'error' => 'invalid'
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
				'error' => 'invalid'
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
	 * @deprecated	5.3 - Always returns isValid = true.
	 */
	public function validatePassword() {
		return [
			'isValid' => true
		];
	}
}
