<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of Argon2.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Argon2 implements IPasswordAlgorithm {
	private const OPTIONS = [
		'memory_cost' => 65536,
		'time_cost' => 4,
		'threads' => 1,
	];
	
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		return \password_verify($password, $hash);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		return \password_hash($password, \PASSWORD_ARGON2I, self::OPTIONS);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return \password_needs_rehash($hash, \PASSWORD_ARGON2I, self::OPTIONS);
	}
}
