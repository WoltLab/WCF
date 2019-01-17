<?php
namespace wcf\util;
use wcf\util\exception\CryptoException;

/**
 * Contains cryptographic helper functions.
 * Features:
 * - Creating secure signatures based on the Keyed-Hash Message Authentication Code algorithm
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 * @since	3.0
 */
final class CryptoUtil {
	/**
	 * Signs the given value with the signature secret.
	 * 
	 * @param	string		$value
	 * @return	string
	 * @throws	CryptoException
	 */
	public static function getSignature($value) {
		if (mb_strlen(SIGNATURE_SECRET, '8bit') < 15) throw new CryptoException('SIGNATURE_SECRET is too short, aborting.');
		
		return hash_hmac('sha256', $value, SIGNATURE_SECRET);
	}

	/**
	 * Creates a signed (signature + encoded value) string.
	 * 
	 * @param	string	$value
	 * @return	string
	 */
	public static function createSignedString($value) {
		return self::getSignature($value).'-'.base64_encode($value);
	}

	/**
	 * Returns whether the given string is a proper signed string.
	 * (i.e. consists of a valid signature + encoded value)
	 * 
	 * @param	string	$string
	 * @return	boolean
	 */
	public static function validateSignedString($string) {
		$parts = explode('-', $string, 2);
		if (count($parts) !== 2) return false;
		list($signature, $value) = $parts;
		$value = base64_decode($value);
		
		return self::secureCompare($signature, self::getSignature($value));
	}

	/**
	 * Returns the value of a signed string, after
	 * validating whether it is properly signed.
	 * 
	 * - Returns null if the string is not properly signed.
	 * 
	 * @param	string		$string
	 * @return	null|string
	 * @see		\wcf\util\CryptoUtil::validateSignedString()
	 */
	public static function getValueFromSignedString($string) {
		if (!self::validateSignedString($string)) return null;
		
		$parts = explode('-', $string, 2);
		return base64_decode($parts[1]);
	}

	/**
	 * @deprecated	Use \hash_equals($known_string, $user_string) directly.
	 */
	public static function secureCompare($hash1, $hash2) {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		$hash1 = (string) $hash1;
		$hash2 = (string) $hash2;
		
		return \hash_equals($hash1, $hash2);
	}
	
	/**
	 * @deprecated	Use \random_bytes($n) directly.
	 */
	public static function randomBytes($n) {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		return random_bytes($n);
	}
	
	/**
	 * @deprecated	Use \random_int($min, $max) directly.
	 */
	public static function randomInt($min, $max) {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		$range = $max - $min;
		if ($range == 0) {
			// not random
			throw new CryptoException("Cannot generate a secure random number, min and max are the same");
		}

		return random_int($min, $max);
	}
	
	/**
	 * Forbid creation of CryptoUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
