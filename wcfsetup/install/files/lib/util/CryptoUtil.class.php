<?php
namespace wcf\util;
use wcf\util\exception\CryptoException;

/**
 * Contains cryptographic helper functions.
 * Features:
 * - Creating secure signatures based on the Keyed-Hash Message Authentication Code algorithm
 * - Constant time comparison function
 * - Generating a string of random bytes
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
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
	 * Compares two strings in a constant time manner.
	 * This function effectively is a polyfill for the PHP 5.6 `hash_equals`.
	 * 
	 * @param	string		$hash1
	 * @param	string		$hash2
	 * @return	boolean
	 */
	public static function secureCompare($hash1, $hash2) {
		$hash1 = (string) $hash1;
		$hash2 = (string) $hash2;
		
		if (function_exists('hash_equals')) {
			return hash_equals($hash1, $hash2);
		}
		
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
	 * Compares a string of N random bytes.
	 * This function effectively is a polyfill for the PHP 7 `random_bytes`.
	 * 
	 * Requires either PHP 7 or 'openssl_random_pseudo_bytes' and throws a CryptoException
	 * if no sufficiently random data could be obtained.
	 * 
	 * @param	int		$n
	 * @return	string
	 * @throws	CryptoException
	 */
	public static function randomBytes($n) {
		try {
			if (function_exists('random_bytes')) {
				$bytes = random_bytes($n);
				if ($bytes === false) throw new CryptoException('Cannot generate a secure stream of bytes.');
				
				return $bytes;
			}
			
			$bytes = openssl_random_pseudo_bytes($n, $s);
			if (!$s) throw new CryptoException('Cannot generate a secure stream of bytes.');
			
			return $bytes;
		}
		catch (CryptoException $e) {
			throw $e;
		}
		catch (\Throwable $e) {
			throw new CryptoException('Cannot generate a secure stream of bytes.', $e);
		}
		catch (\Exception $e) {
			throw new CryptoException('Cannot generate a secure stream of bytes.', $e);
		}
	}
	
	/**
	 * Forbid creation of CryptoUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
