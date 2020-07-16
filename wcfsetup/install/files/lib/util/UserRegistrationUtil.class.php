<?php
namespace wcf\util;

/**
 * Contains user registration related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class UserRegistrationUtil {
	/**
	 * Forbid creation of StringUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
	
	/**
	 * Returns true if the given name is a valid username.
	 * 
	 * @param	string		$name		username
	 * @return	boolean
	 */
	public static function isValidUsername($name) {
		if (!UserUtil::isValidUsername($name)) return false;
		
		$length = mb_strlen($name);
		if ($length < REGISTER_USERNAME_MIN_LENGTH || $length > REGISTER_USERNAME_MAX_LENGTH) return false;
		
		if (!self::checkForbiddenUsernames($name)) return false;
		
		if (REGISTER_USERNAME_FORCE_ASCII) {
			if (!preg_match('/^[\x20-\x7E]+$/', $name)) return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if the given e-mail is a valid address.
	 * 
	 * @param	string		$email
	 * @return	boolean
	 */
	public static function isValidEmail($email) {
		return (UserUtil::isValidEmail($email) && self::checkForbiddenEmails($email));
	}
	
	/**
	 * Returns false if the given name is a forbidden username.
	 * 
	 * @param	string		$name
	 * @return	boolean
	 */
	public static function checkForbiddenUsernames($name) {
		return StringUtil::executeWordFilter($name, REGISTER_FORBIDDEN_USERNAMES);
	}
	
	/**
	 * Returns false if the given email is a forbidden email.
	 * 
	 * @param	string		$email
	 * @return	boolean
	 */
	public static function checkForbiddenEmails($email) {
		return (StringUtil::executeWordFilter($email, REGISTER_FORBIDDEN_EMAILS) && (!StringUtil::trim(REGISTER_ALLOWED_EMAILS) || !StringUtil::executeWordFilter($email, REGISTER_ALLOWED_EMAILS)));
	}
	
	/**
	 * Always returns true.
	 * 
	 * @deprecated	5.3 - Take a look at the zxcvbn verdict from WoltLabSuite/Core/Ui/User/PasswordStrength.
	 */
	public static function isSecurePassword($password) {
		return true;
	}
	
	/**
	 * Returns the `passwordrules` attribute value. 
	 * 
	 * @see         https://developer.apple.com/password-rules/
	 * @return	string
	 */
	public static function getPasswordRulesAttributeValue() {
		return "minlength:8;";
	}
	
	/**
	 * Generates a random activation code with the given length.
	 * Warning: A length greater than 9 is out of integer range.
	 * 
	 * @param	integer		$length
	 * @return	integer
	 */
	public static function getActivationCode($length = 9) {
		return MathUtil::getRandomValue(pow(10, $length - 1), pow(10, $length) - 1);
	}
	
	/**
	 * Generates a random field name.
	 * 
	 * @param	string		$fieldName
	 * @return	string
	 */
	public static function getRandomFieldName($fieldName) {
		$hash = StringUtil::getRandomID();
		return substr($hash, 0, mt_rand(8, 16));
	}
}
