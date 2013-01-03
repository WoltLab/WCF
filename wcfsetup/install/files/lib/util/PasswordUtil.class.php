<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\Regex;

/**
 * Provides functions to compute password hashes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class PasswordUtil {
	/**
	 * concated list of valid blowfish salt characters
	 * @var	string
	 */
	private static $blowfishCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
	
	/**
	 * list of supported encryption type by software identifier
	 * @var	array<string>
	 */
	private static $supportedEncryptionTypes = array(
		'ipb2',		// Invision Power Board 2.x
		'ipb3',		// Invision Power Board 3.x
		'mybb1',	// MyBB 1.x
		'smf1',		// Simple Machines Forum 1.x
		'smf2',		// Simple Machines Forum 2.x
		'vb3',		// vBulletin 3.x
		'vb4',		// vBulletin 4.x
		'vb5',		// vBulletin 5.x
		'wbb2',		// WoltLab Burning Board 2.x
		'wcf1',		// WoltLab Community Framework 1.x
		'wcf2',		// WoltLab Community Framework 2.x
		'xf1'		// XenForo 1.x
	);
	
	/**
	 * blowfish cost factor
	 * @var	string
	 */
	const BCRYPT_COST = '08';
	
	/**
	 * blowfish encryption type
	 * @var	string
	 */
	const BCRYPT_TYPE = '2a';
	
	/**
	 * Returns true, if given encryption type is supported.
	 * 
	 * @param	string		$type
	 * @return	boolean
	 */
	public static function isSupported($type) {
		return in_array($type, self::$supportedEncryptionTypes);
	}
	
	/**
	 * Returns true, if given hash looks like a valid bcrypt hash.
	 * 
	 * @param	string		$hash
	 * @return	boolean
	 */
	public static function isBlowfish($hash) {
		return (Regex::compile('^\$2[afx]\$')->match($hash) ? true : false);
	}
	
	/**
	 * Returns true, if given bcrypt hash uses a different cost factor and should be re-computed.
	 * 
	 * @param	string		$hash
	 * @return	boolean
	 */
	public static function isDifferentBlowfish($hash) {
		$currentCost = intval(self::BCRYPT_COST);
		$hashCost = intval(substr($hash, 4, 2));
		
		if ($currentCost != $hashCost) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates password against stored hash, encryption type is automatically resolved.
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	public static function checkPassword($username, $password, $dbHash) {
		$type = self::detectEncryption($dbHash);
		if ($type === 'unknown') {
			throw new SystemException("Unable to determine password encryption");
		}
		
		// drop type from hash
		$dbHash = substr($dbHash, strlen($type));
		
		// check for salt
		$salt = '';
		if (($pos = strrpos($dbHash, ':')) !== false) {
			$salt = substr(substr($dbHash, $pos), 1);
			$dbHash = substr($dbHash, 1, ($pos - 1));
		}
		
		// compare hash
		return call_user_func('\wcf\util\PasswordUtil::'.$type, $username, $password, $salt, $dbHash);
	}
	
	/**
	 * Returns encryption type if possible.
	 * 
	 * @param	string		$hash
	 * @return	string
	 */
	public static function detectEncryption($hash) {
		if (($pos = strpos($hash, ':')) !== false) {
			$type = substr($hash, 0, $pos);
			if (in_array($type, self::$supportedEncryptionTypes)) {
				return $type;
			}
		}
		
		return 'unknown';
	}
	
	/**
	 * Returns a double salted bcrypt hash.
	 * 
	 * @param	string		$password
	 * @param	string		$salt
	 * @return	string
	 */
	public static function getDoubleSaltedHash($password, $salt = null) {
		if ($salt === null) {
			$salt = self::getRandomSalt();
		}
		
		return self::getSaltedHash(self::getSaltedHash($password, $salt), $salt);
	}
	
	/**
	 * Returns a simple salted bcrypt hash.
	 * 
	 * @param	string		$password
	 * @param	string		$salt
	 * @return	string
	 */
	public static function getSaltedHash($password, $salt = null) {
		if ($salt === null) {
			$salt = self::getRandomSalt();
		}
		
		return crypt($password, self::getSalt($salt));
	}
	
	/**
	 * Returns a random blowfish-compatible salt.
	 * 
	 * @return	string
	 */
	public static function getRandomSalt() {
		$salt = '';
		
		for ($i = 0, $maxIndex = (strlen(self::$blowfishCharacters) - 1); $i < 22; $i++) {
			$salt .= self::$blowfishCharacters[mt_rand(0, $maxIndex)];
		}
		
		return $salt;
	}
	
	/**
	 * Generates a random user password with the given character length.
	 * 
	 * @param	integer		$length
	 * @return	string
	 */
	public static function getRandomPassword($length = 8) {
		$availableCharacters = array(
			'abcdefghijklmnopqrstuvwxyz',
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'0123456789',
			'+#-.,;:?!'
		);
		
		$password = '';
		$type = 0;
		for ($i = 0; $i < $length; $i++) {
			$type = ($i % 4 == 0) ? 0 : ($type + 1);
			$password .= substr($availableCharacters[$type], MathUtil::getRandomValue(0, strlen($availableCharacters[$type]) - 1), 1);
		}
		
		return str_shuffle($password);
	}
	
	/**
	 * Returns a blowfish salt, e.g. $2a$07$usesomesillystringforsalt$
	 * 
	 * @param	string		$salt
	 * @return	string
	 */
	protected static function getSalt($salt) {
		$salt = StringUtil::substring($salt, 0, 22);
		
		return '$' . self::BCRYPT_TYPE . '$' . self::BCRYPT_COST . '$' . $salt;
	}
	
	/**
	 * Validates the password hash for Invision Power Board 2.x (ipb2).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function ipb2($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * Validates the password hash for Invision Power Board 3.x (ipb3).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function ipb3($username, $password, $salt, $dbHash) {
		return ($dbHash == md5(md5($salt) . md5($password)));
	}
	
	/**
	 * Validates the password hash for MyBB 1.x (mybb1).
	 *
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function mybb1($username, $password, $salt, $dbHash) {
		return ($dbHash == md5(md5($salt) . md5($password)));
	}
	
	/**
	 * Validates the password hash for Simple Machines Forums 1.x (smf1).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function smf1($username, $password, $salt, $dbHash) {
		return ($dbHash == sha1(StringUtil::toLowerCase($username) . $password));
	}
	
	/**
	 * Validates the password hash for Simple Machines Forums 2.x (smf2).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function smf2($username, $password, $salt, $dbHash) {
		return self::smf1($username, $password, $salt, $dbHash);
	}
	
	/**
	 * Validates the password hash for vBulletin 3 (vb3).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function vb3($username, $password, $salt, $dbHash) {
		return ($dbHash == md5(md5($password) . $salt));
	}
	
	/**
	 * Validates the password hash for vBulletin 4 (vb4).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function vb4($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * Validates the password hash for vBulletin 5 (vb5).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function vb5($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * Validates the password hash for WoltLab Burning Board 2 (wbb2).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function wbb2($username, $password, $salt, $dbHash) {
		if ($dbHash == md5($password)) {
			return true;
		}
		else if ($dbHash == sha1($password)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates the password hash for WoltLab Community Framework 1.x (wcf1).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function wcf1($username, $password, $salt, $dbHash) {
		return ($dbHash == sha1($salt . sha1($salt . sha1($password))));
	}
	
	/**
	 * Validates the password hash for WoltLab Community Framework 2.x (wcf2).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function wcf2($username, $password, $salt, $dbHash) {
		return ($dbHash == self::getDoubleSaltedHash($password, $salt));
	}
	
	/**
	 * Validates the password hash for XenForo 1.x with (xf1).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function xf1($username, $password, $salt, $dbHash) {
		if ($dbHash == sha1(sha1($password) . $salt)) {
			return true;
		}
		else if (extension_loaded('hash')) {
			return ($dbHash == hash('sha256', hash('sha256', $password) . $salt));
		}
		
		return false;
	}
	
	private function __construct() { }
}
