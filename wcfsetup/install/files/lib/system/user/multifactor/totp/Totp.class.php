<?php
namespace wcf\system\user\multifactor\totp;

/**
 * Implementation of the Time-based One-time Password Algorithm (RFC 6238).
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor\Totp
 * @since	5.4
 */
final class Totp {
	/**
	 * The number of digits of the resulting code.
	 */
	public const CODE_LENGTH = 6;
	
	/**
	 * The number of seconds after which the internal counter increases.
	 */
	private const TIME_STEP = 30;
	
	/**
	 * The number of additional time steps allowed into each direction.
	 * 
	 * `2` into each direction allows a total of 5`` codes in total.
	 */
	private const LEEWAY = 2;
	
	/**
	 * @var string
	 */
	private $secret;
	
	public function __construct(string $secret) {
		$this->secret = $secret;
	}
	
	/**
	 * Returns a random secret.
	 */
	public static function generateSecret(): string {
		return \random_bytes(16);
	}
	
	/**
	 * Generates the HOTP code for the given counter.
	 */
	private function generateHotpCode(int $counter): string {
		$hash = \hash_hmac('sha1', \pack('J', $counter), $this->secret, true);
		$offset = \ord($hash[\mb_strlen($hash, '8bit') - 1]) & 0xf;
		$binary =
			((\ord($hash[$offset + 0]) & 0x7f) << 24) |
			((\ord($hash[$offset + 1]) & 0xff) << 16) |
			((\ord($hash[$offset + 2]) & 0xff) << 8) |
			((\ord($hash[$offset + 3]) & 0xff) << 0);
		
		$otp = \str_pad($binary % (10 ** self::CODE_LENGTH), self::CODE_LENGTH, "0", \STR_PAD_LEFT);
		
		return $otp;
	}
	
	/**
	 * Generates the TOTP code for the given timestamp.
	 */
	public function generateTotpCode(\DateTime $time): string {
		$counter = \intval($time->getTimestamp() / self::TIME_STEP);
		
		return $this->generateHotpCode($counter);
	}
	
	/**
	 * Validates the given userCode against the given minimum counter and time.
	 * 
	 * If this method returns `true` the $minCounter value will be updated to the counter that
	 * was used for verification. You MUST store the updated $minCounter to prevent code re-use.
	 */
	public function validateTotpCode(string $userCode, int &$minCounter, \DateTime $time): bool {
		$counter = \intval($time->getTimestamp() / self::TIME_STEP);
		
		for ($offset = -self::LEEWAY; $offset < self::LEEWAY; $offset++) {
			$possibleCode = $this->generateHotpCode($counter + $offset);
			
			if (\hash_equals($possibleCode, $userCode)) {
				// Check for possible code re-use.
				if ($counter + $offset > $minCounter) {
					$minCounter = $counter + $offset;
					return true;
				}
				
				return false;
			}
		}
		
		return false;
	}
}
