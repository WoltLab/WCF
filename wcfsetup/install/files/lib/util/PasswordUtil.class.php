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
		'phpbb3',	// phpBB 3.x
		'smf1',		// Simple Machines Forum 1.x
		'smf2',		// Simple Machines Forum 2.x
		'vb3',		// vBulletin 3.x
		'vb4',		// vBulletin 4.x
		'vb5',		// vBulletin 5.x
		'wbb2',		// WoltLab Burning Board 2.x
		'wcf1',		// WoltLab Community Framework 1.x
		'wcf2',		// WoltLab Community Framework 2.x
		'xf1',		// XenForo 1.x
		'joomla1',	// Joomla 1.x
		'joomla2',	// Joomla 2.x
		'joomla3',	// Joomla 3.x
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
	 * Returns true if given encryption type is supported.
	 * 
	 * @param	string		$type
	 * @return	boolean
	 */
	public static function isSupported($type) {
		return in_array($type, self::$supportedEncryptionTypes);
	}
	
	/**
	 * Returns true if given hash looks like a valid bcrypt hash.
	 * 
	 * @param	string		$hash
	 * @return	boolean
	 */
	public static function isBlowfish($hash) {
		return (Regex::compile('^\$2[afx]\$')->match($hash) ? true : false);
	}
	
	/**
	 * Returns true if given bcrypt hash uses a different cost factor and should be re-computed.
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
		$dbHash = substr($dbHash, strlen($type) + 1);
		
		// check for salt
		$salt = '';
		if (($pos = strrpos($dbHash, ':')) !== false) {
			$salt = substr(substr($dbHash, $pos), 1);
			$dbHash = substr($dbHash, 0, $pos);
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
		
		return crypt($password, $salt);
	}
	
	/**
	 * Returns a random blowfish-compatible salt.
	 * 
	 * @return	string
	 */
	public static function getRandomSalt() {
		$salt = '';
		
		for ($i = 0, $maxIndex = (strlen(self::$blowfishCharacters) - 1); $i < 22; $i++) {
			$salt .= self::$blowfishCharacters[self::secureRandomNumber(0, $maxIndex)];
		}
		
		return self::getSalt($salt);
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
			$password .= substr($availableCharacters[$type], self::secureRandomNumber(0, strlen($availableCharacters[$type]) - 1), 1);
		}
		
		return str_shuffle($password);
	}
	
	/**
	 * Compares two password hashes. This function is protected against timing attacks.
	 * 
	 * @see		http://codahale.com/a-lesson-in-timing-attacks/
	 * 
	 * @param	string		$hash1
	 * @param	string		$hash2
	 * @return	boolean
	 */
	public static function secureCompare($hash1, $hash2) {
		if (strlen($hash1) !== strlen($hash2)) {
			return false;
		}
	
		$result = 0;
		for ($i = 0, $length = strlen($hash1); $i < $length; $i++) {
			$result |= ord($hash1[$i]) ^ ord($hash2[$i]);
		}
	
		return ($result === 0);
	}
	
	/**
	 * Generates secure random numbers using OpenSSL.
	 * 
	 * @see		http://de1.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
	 * @param	integer		$min
	 * @param	integer		$max
	 * @return	integer
	 */
	public static function secureRandomNumber($min, $max) {
		$range = $max - $min;
		if ($range == 0) {
			// not random
			throw new SystemException("Cannot generate a secure random number, min and max are the same");
		}
		
		// fallback to mt_rand() if OpenSSL is not available
		if (!function_exists('openssl_random_pseudo_bytes')) {
			return mt_rand($min, $max);
		}
		
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		}
		while ($rnd >= $range);
		
		return $min + $rnd;
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
		return self::secureCompare($dbHash, md5(md5($salt) . md5($password)));
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
		return self::secureCompare($dbHash, md5(md5($salt) . md5($password)));
	}
	
	/**
	 * Validates the password hash for phpBB 3.x (phpbb3).
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function phpbb3($username, $password, $salt, $dbHash) {
		if (StringUtil::length($dbHash) !== 34) {
			return self::secureCompare(md5($password), $dbHash);
		}
		
		$hash_crypt_private = function ($password, $setting) {
			static $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			
			$output = '*';
			
			// Check for correct hash
			if (substr($setting, 0, 3) !== '$H$' && substr($setting, 0, 3) !== '$P$') {
				return $output;
			}
			
			$count_log2 = strpos($itoa64, $setting[3]);
			
			if ($count_log2 < 7 || $count_log2 > 30) {
				return $output;
			}
			
			$count = 1 << $count_log2;
			$salt = substr($setting, 4, 8);
			
			if (strlen($salt) != 8) {
				return $output;
			}
			
			$hash = md5($salt . $password, true);
			do {
				$hash = md5($hash . $password, true);
			}
			while (--$count);
			
			$output = substr($setting, 0, 12);
			$hash_encode64 = function ($input, $count, &$itoa64) {
				$output = '';
				$i = 0;
				
				do {
					$value = ord($input[$i++]);
					$output .= $itoa64[$value & 0x3f];
					
					if ($i < $count) {
						$value |= ord($input[$i]) << 8;
					}
					
					$output .= $itoa64[($value >> 6) & 0x3f];
					
					if ($i++ >= $count) {
						break;
					}
					
					if ($i < $count) {
						$value |= ord($input[$i]) << 16;
					}
					
					$output .= $itoa64[($value >> 12) & 0x3f];
					
					if ($i++ >= $count) {
						break;
					}
					
					$output .= $itoa64[($value >> 18) & 0x3f];
				}
				while ($i < $count);
				
				return $output;
			};
			
			$output .= $hash_encode64($hash, 16, $itoa64);
			
			return $output;
		};
		
		return self::secureCompare($hash_crypt_private($password, $dbHash), $dbHash);
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
		return self::secureCompare($dbHash, sha1(StringUtil::toLowerCase($username) . $password));
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
		return self::secureCompare($dbHash, md5(md5($password) . $salt));
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
		if (self::secureCompare($dbHash, md5($password))) {
			return true;
		}
		else if (self::secureCompare($dbHash, sha1($password))) {
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
		return self::secureCompare($dbHash, sha1($salt . sha1($salt . sha1($password))));
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
		return self::secureCompare($dbHash, self::getDoubleSaltedHash($password, $salt));
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
		if (self::secureCompare($dbHash, sha1(sha1($password) . $salt))) {
			return true;
		}
		else if (extension_loaded('hash')) {
			return self::secureCompare($dbHash, hash('sha256', hash('sha256', $password) . $salt));
		}
		
		return false;
	}
	
	/**
	 * Validates the password hash for Joomla 1.x (kunea)
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function joomla1($username, $password, $salt, $dbHash) {
		if (self::secureCompare($dbHash, md5($password . $salt))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validates the password hash for Joomla 2.x (kunea)
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function joomla2($username, $password, $salt, $dbHash) {
		return self::joomla1($username, $password, $salt, $dbHash);
	}
	
	/**
	 * Validates the password hash for Joomla 3.x (kunea)
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$salt
	 * @param	string		$dbHash
	 * @return	boolean
	 */
	protected static function joomla3($username, $password, $salt, $dbHash) {
		return self::joomla1($username, $password, $salt, $dbHash);
	}
	
	private function __construct() { }
}
