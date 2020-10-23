<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for WoltLab Burning Board 2 (wbb2).
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Wbb2 implements IPasswordAlgorithm {
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		if (\hash_equals($hash, \md5($password))) {
			return true;
		}
		else if (\hash_equals($hash, \sha1($password))) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		return \sha1($password);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return false;
	}
}
