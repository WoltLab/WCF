<?php
namespace wcf\system\user\authentication\password;

/**
 * Implementation of a password algorithm, modelled after PHP's password_* API.
 * 
 * This is used for password compatibility after importing from a third party software.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password
 * @since	5.4
 */
interface IPasswordAlgorithm {
	/**
	 * Returns whether the given $password matches the given $hash.
	 */
	public function verify(string $password, string $hash): bool;
	
	/**
	 * Returns a hash of the given $password.
	 */
	public function hash(string $password): string;
	
	/**
	 * Returns whether the given $hash still matches the configured security parameters.
	 */
	public function needsRehash(string $hash): bool;
}
