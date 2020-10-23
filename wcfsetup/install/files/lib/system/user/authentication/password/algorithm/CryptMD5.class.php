<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for MD5 mode of crypt().
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class CryptMD5 implements IPasswordAlgorithm {
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		// The passwords are stored differently when importing. Sometimes they are saved with the salt,
		// but sometimes also without the salt. We don't need the salt, because the salt is saved with the hash. 
		[$hash] = \explode(':', $hash, 2);
		
		return \hash_equals($hash, $this->hashWithSalt($password, $hash));
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		$salt = '$1$'.\bin2hex(\random_bytes(6)).'$';
		
		return $this->hashWithSalt($password, $salt);
	}
	
	/**
	 * Returns the hashed password, hashed with a given salt.
	 */
	private function hashWithSalt(string $password, string $salt): string {
		return \crypt($password, $salt);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return false;
	}
}
