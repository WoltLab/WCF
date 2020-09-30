<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for vBulletin 5 (vb5).
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Vb5 implements IPasswordAlgorithm {
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		[$hash, $salt] = \explode(':', $hash, 2);
		
		return \hash_equals($hash, $this->hashWithSalt($password, $salt));
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		$salt = \bin2hex(\random_bytes(20));
		
		return $this->hashWithSalt($password, $salt).':'.$salt;
	}
	
	/**
	 * Returns the hashed password, hashed with a given salt.
	 */
	private function hashWithSalt(string $password, string $salt): string {
		return \md5(\md5($password) . $salt);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return false;
	}
}
