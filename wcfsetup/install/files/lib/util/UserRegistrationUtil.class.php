<?php
namespace wcf\util;

/**
 * Contains user registration related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * Returns true if the given password is secure.
	 * 
	 * @param	string		$password
	 * @return	boolean
	 */
	public static function isSecurePassword($password) {
		if (REGISTER_ENABLE_PASSWORD_SECURITY_CHECK) {
			if (mb_strlen($password) < REGISTER_PASSWORD_MIN_LENGTH) return false;
			
			if (REGISTER_PASSWORD_MUST_CONTAIN_DIGIT && !preg_match('![0-9]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_LOWER_CASE && !preg_match('![a-z]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_UPPER_CASE && !preg_match('![A-Z]+!', $password)) return false;
			if (REGISTER_PASSWORD_MUST_CONTAIN_SPECIAL_CHAR && !preg_match('![^A-Za-z0-9]+!', $password)) return false;
		}
		
		return true;
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
		$hash = StringUtil::getHash($fieldName . StringUtil::getRandomID());
		return substr($hash, 0, mt_rand(8, 16));
	}
}
