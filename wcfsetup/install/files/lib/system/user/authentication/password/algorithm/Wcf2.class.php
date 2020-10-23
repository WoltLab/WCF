<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for WoltLab Community Framework 2.x (wcf2).
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Wcf2 implements IPasswordAlgorithm {
	/**
	 * @var DoubleBcrypt 
	 */
	private $doubleBcrypt;
	
	/**
	 * Wcf2 constructor.
	 */
	public function __construct() {
		$this->doubleBcrypt = new DoubleBcrypt();
	}
	
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		return $this->doubleBcrypt->verify($password, $hash);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hash(string $password): string {
		return $this->doubleBcrypt->hash($password);
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return $this->doubleBcrypt->needsRehash($hash);
	}
}
