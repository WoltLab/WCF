<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\exception\CryptoException;

/**
 * Provides functions to compute password hashes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class PasswordUtil {
	/**
	 * list of possible characters in generated passwords
	 * @var	string
	 */
	const PASSWORD_CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	private static $blowfishCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
	
	/**
	 * list of supported encryption type by software identifier
	 * @var	string[]
	 */
	private static $supportedEncryptionTypes = [
		'argon2',	// vBulletin 5.x
		'ipb2',		// Invision Power Board 2.x
		'ipb3',		// Invision Power Board 3.x
		'mybb1',	// MyBB 1.x
		'phpbb3',	// phpBB 3.x
		'phpass',	// phpass Portable Hashes
		'smf1',		// Simple Machines Forum 1.x
		'smf2',		// Simple Machines Forum 2.x
		'vb3',		// vBulletin 3.x
		'vb4',		// vBulletin 4.x
		'vb5',		// vBulletin 5.x
		'wbb2',		// WoltLab Burning Board 2.x
		'wcf1',		// WoltLab Community Framework 1.x
		'wcf2',		// WoltLab Suite 3.x / WoltLab Community Framework 2.x
		'xf1',		// XenForo 1.0 / 1.1
		'xf12',		// XenForo 1.2+
		'joomla1',	// Joomla 1.x
		'joomla2',	// Joomla 2.x
		'joomla3',	// Joomla 3.x
		'phpfox3',	// phpFox 3.x
		'cryptMD5',
		'invalid',	// Never going to match anything
	];
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	const BCRYPT_COST = '08';
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	const BCRYPT_TYPE = '2a';
	
	/**
	 * Returns true if given encryption type is supported.
	 * 
	 * @param	string		$type
	 * @return	bool
	 */
	public static function isSupported($type) {
		if (in_array($type, self::$supportedEncryptionTypes)) {
			return true;
		}
		
		if (preg_match('~^wcf1e[cms][01][ab][01]$~', $type)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	public static function isBlowfish($hash) {
		return (Regex::compile('^\$2[afxy]\$')->match($hash) ? true : false);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
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
	 * @return	bool
	 * @throws	SystemException
	 */
	public static function checkPassword($username, $password, $dbHash) {
		$type = self::detectEncryption($dbHash);
		if ($type === 'unknown') {
			throw new SystemException("Unable to determine password encryption");
		}
		
		// drop type from hash
		$dbHash = substr($dbHash, strlen($type) + 1);
		
		// check for salt
		$parts = explode(':', $dbHash, 2);
		if (count($parts) == 2) {
			list($dbHash, $salt) = $parts;
		}
		else {
			$dbHash = $parts[0];
			$salt = '';
		}
		
		// compare hash
		if (in_array($type, self::$supportedEncryptionTypes)) {
			return call_user_func('\wcf\util\PasswordUtil::'.$type, $username, $password, $salt, $dbHash);
		}
		else {
			// WCF 1.x with different encryption
			return self::wcf1e($type, $password, $salt, $dbHash);
		}
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
			if (self::isSupported($type)) {
				return $type;
			}
		}
		
		return 'unknown';
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	public static function getDoubleSaltedHash($password, $salt = null) {
		if ($salt === null) {
			$salt = self::getRandomSalt();
		}
		
		return self::getSaltedHash(self::getSaltedHash($password, $salt), $salt);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	public static function getSaltedHash($password, $salt = null) {
		if ($salt === null) {
			$salt = self::getRandomSalt();
		}
		
		return crypt($password, $salt);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	public static function getRandomSalt() {
		$salt = '';
		
		for ($i = 0, $maxIndex = (strlen(self::$blowfishCharacters) - 1); $i < 22; $i++) {
			$salt .= self::$blowfishCharacters[self::secureRandomNumber(0, $maxIndex)];
		}
		
		return self::getSalt($salt);
	}
	
	/**
	 * Generates a random alphanumeric user password with the given character length.
	 * 
	 * @param	int		$length
	 * @return	string
	 */
	public static function getRandomPassword($length = 12) {
		$charset = self::PASSWORD_CHARSET;
		$password = '';
		
		for ($i = 0, $maxIndex = (strlen($charset) - 1); $i < $length; $i++) {
			$password .= $charset[self::secureRandomNumber(0, $maxIndex)];
		}
		
		return $password;
	}
	
	/**
	 * @deprecated	Use \wcf\util\CryptoUtil::secureCompare()
	 */
	public static function secureCompare($hash1, $hash2) {
		return \hash_equals($hash1, $hash2);
	}
	
	/**
	 * @deprecated	Use random_int()
	 */
	public static function secureRandomNumber($min, $max) {
		$range = $max - $min;
		if ($range == 0) {
			// not random
			throw new SystemException("Cannot generate a secure random number, min and max are the same");
		}

		try {
			return CryptoUtil::randomInt($min, $max);
		}
		catch (CryptoException $e) {
			// Backwards compatibility: This function never did throw.
			return mt_rand($min, $max);
		}
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function getSalt($salt) {
		$salt = mb_substr($salt, 0, 22);
		
		return '$' . self::BCRYPT_TYPE . '$' . self::BCRYPT_COST . '$' . $salt;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function argon2($username, $password, $salt, $dbHash) {
		return password_verify($password, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function ipb2($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function ipb3($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, md5(md5($salt) . md5($password)));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function mybb1($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, md5(md5($salt) . md5($password)));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	public static function phpbb3($username, $password, $salt, $dbHash) {
		$phpassResult = self::phpass($username, $password, $salt, $dbHash);
		
		if ($phpassResult) {
			return true;
		}
		
		if (!preg_match('/^\$([^$]+)\$/', $dbHash, $matches)) {
			return false;
		}
		
		$algorithms = explode('\\', $matches[1]);
		// Strip the type prefix.
		$dbHash = substr($dbHash, strlen($matches[0]));
		
		// The following loop only supports the multi-hash variant.
		// Everything else should already be handled at this point.
		if (count($algorithms) == 1) {
			return false;
		}
		foreach ($algorithms as $algorithm) {
			$dollar = strpos($dbHash, '$');
			if ($dollar === false) {
				return false;
			}
			$settings = '$'.$algorithm.'$'.str_replace('\\', '$', substr($dbHash, 0, $dollar));
			$dbHash = substr($dbHash, $dollar + 1);
			
			switch ($algorithm) {
				case 'H':
				case 'P':
					$password = str_replace($settings, '', self::phpassHashCryptPrivate($password, $settings));
				break;
				case '2a':
				case '2y':
					$password = str_replace($settings, '', self::getSaltedHash($password, $settings));
				break;
			}
		}
		
		return \hash_equals($dbHash, $password);
	}
	
	private static function phpassHashCryptPrivate($password, $setting) {
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
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function phpass($username, $password, $salt, $dbHash) {
		if (mb_strlen($dbHash) !== 34) {
			return \hash_equals(md5($password), $dbHash);
		}
		
		return \hash_equals(self::phpassHashCryptPrivate($password, $dbHash), $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function smf1($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, sha1(mb_strtolower($username) . $password));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function smf2($username, $password, $salt, $dbHash) {
		return self::smf1($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function vb3($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, md5(md5($password) . $salt));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function vb4($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function vb5($username, $password, $salt, $dbHash) {
		return self::vb3($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function wbb2($username, $password, $salt, $dbHash) {
		if (\hash_equals($dbHash, md5($password))) {
			return true;
		}
		else if (\hash_equals($dbHash, sha1($password))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function wcf1($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, sha1($salt . sha1($salt . sha1($password))));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function wcf1e($type, $password, $salt, $dbHash) {
		preg_match('~^wcf1e([cms])([01])([ab])([01])$~', $type, $matches);
		$enableSalting = $matches[2];
		$saltPosition = $matches[3];
		$encryptBeforeSalting = $matches[4];
		
		$encryptionMethod = '';
		switch ($matches[1]) {
			case 'c':
				$encryptionMethod = 'crc32';
			break;
			
			case 'm':
				$encryptionMethod = 'md5';
			break;
			
			case 's':
				$encryptionMethod = 'sha1';
			break;
		}
		
		$hash = '';
		if ($enableSalting) {
			if ($saltPosition == 'b') {
				$hash .= $salt;
			}
			
			if ($encryptBeforeSalting) {
				$hash .= $encryptionMethod($password);
			}
			else {
				$hash .= $password;
			}
			
			if ($saltPosition == 'a') {
				$hash .= $salt;
			}
			
			$hash = $encryptionMethod($hash);
		}
		else {
			$hash = $encryptionMethod($password);
		}
		$hash = $encryptionMethod($salt . $hash);
		
		return \hash_equals($dbHash, $hash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function wcf2($username, $password, $salt, $dbHash) {
		return \hash_equals($dbHash, self::getDoubleSaltedHash($password, $dbHash));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function xf1($username, $password, $salt, $dbHash) {
		if (\hash_equals($dbHash, sha1(sha1($password) . $salt))) {
			return true;
		}
		
		return \hash_equals($dbHash, hash('sha256', hash('sha256', $password) . $salt));
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function xf12($username, $password, $salt, $dbHash) {
		if (\hash_equals($dbHash, self::getSaltedHash($password, $dbHash))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function joomla1($username, $password, $salt, $dbHash) {
		if (\hash_equals($dbHash, md5($password . $salt))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function joomla2($username, $password, $salt, $dbHash) {
		return self::joomla1($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function joomla3($username, $password, $salt, $dbHash) {
		return self::joomla1($username, $password, $salt, $dbHash);
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	 protected static function phpfox3($username, $password, $salt, $dbHash) {
		 if (\hash_equals($dbHash, md5(md5($password) . md5($salt)))) {
			 return true;
		 }
		 
		 return false;
	 }
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function cryptMD5($username, $password, $salt, $dbHash) {
		if (\hash_equals($dbHash, self::getSaltedHash($password, $dbHash))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @deprecated	5.4 - Use the new password algorithm framework in \wcf\system\user\authentication\password\*.
	 */
	protected static function invalid($username, $password, $salt, $dbHash) {
		return false;
	}
	
	/**
	 * Forbid creation of PasswordUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
