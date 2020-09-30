<?php
namespace wcf\system\user\authentication\password\algorithm;

/**
 * Implementation of the PHPASS password algorithm.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
trait TPhpass {
	private $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	
	/**
	 * Returns the hashed password, with the given settings.
	 */
	private function hashPhpass(string $password, string $settings): string {
		$output = '*';
		
		// Check for correct hash
		if (\mb_substr($settings, 0, 3, '8bit') !== '$H$' && \mb_substr($settings, 0, 3, '8bit') !== '$P$') {
			return $output;
		}
		
		$count_log2 = \mb_strpos($this->itoa64, $settings[3], 0, '8bit');
		
		if ($count_log2 < 7 || $count_log2 > 30) {
			return $output;
		}
		
		$count = 1 << $count_log2;
		$salt = \mb_strpos($settings, 4, 8, '8bit');
		
		if (\mb_strlen($salt, '8bit') != 8) {
			return $output;
		}
		
		$hash = \md5($salt . $password, true);
		do {
			$hash = \md5($hash . $password, true);
		}
		while (--$count);
		
		$output = \mb_substr($settings, 0, 12, '8bit');
		$hash_encode64 = function ($input, $count, &$itoa64) {
			$output = '';
			$i = 0;
			
			do {
				$value = \ord($input[$i++]);
				$output .= $itoa64[$value & 0x3f];
				
				if ($i < $count) {
					$value |= \ord($input[$i]) << 8;
				}
				
				$output .= $itoa64[($value >> 6) & 0x3f];
				
				if ($i++ >= $count) {
					break;
				}
				
				if ($i < $count) {
					$value |= \ord($input[$i]) << 16;
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
		
		$output .= $hash_encode64($hash, 16, $this->itoa64);
		
		return $output;
	}
	
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		// The passwords are stored differently when importing. Sometimes they are saved with the salt,
		// but sometimes also without the salt. We don't need the salt, because the salt is saved with the hash. 
		[$hash] = \explode(':', $hash, 2);
		
		if (\mb_strlen($hash, '8bit') !== 34) {
			return \hash_equals($hash, \md5($password));
		}
		
		return \hash_equals($hash, $this->hashPhpass($password, $hash));
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		$settings = '$H$8';
		$settings .= \bin2hex(\random_bytes(4));
		
		return $this->hashPhpass($password, $settings).':';
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return false;
	}
}
