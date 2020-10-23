<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of BCrypt.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Bcrypt implements IPasswordAlgorithm {
	private const OPTIONS = [
		'cost' => 10,
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
		return \password_hash($password, \PASSWORD_BCRYPT, self::OPTIONS);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return \password_needs_rehash($hash, \PASSWORD_BCRYPT, self::OPTIONS);
	}
}
