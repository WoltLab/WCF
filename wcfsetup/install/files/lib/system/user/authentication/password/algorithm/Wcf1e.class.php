<?php
namespace wcf\system\user\authentication\password\algorithm;
use wcf\system\user\authentication\password\IPasswordAlgorithm;

/**
 * Implementation of the password algorithm for WoltLab Community Framework 1.x with different encryption (wcf1e).
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password\Algorithm
 * @since	5.4
 */
final class Wcf1e implements IPasswordAlgorithm {
	/**
	 * @var string
	 */
	private $encryptionMethod;
	
	/**
	 * @var bool 
	 */
	private $enableSalting;
	
	/**
	 * @var string
	 */
	private $saltPosition;
	
	/**
	 * @var bool
	 */
	private $encryptBeforeSalting;
	
	/**
	 * Wcf1e constructor.
	 */
	public function __construct(string $type) {
		if (preg_match('~^wcf1e([cms])([01])([ab])([01])$~', $type, $matches) === false) {
			throw new \BadMethodCallException("The type '". $type ."' is invalid.");
		}
		
		$this->encryptionMethod = $matches[1];
		$this->enableSalting = (bool) $matches[2];
		$this->saltPosition = $matches[3];
		$this->encryptBeforeSalting = (bool) $matches[4];
	}
	
	/**
	 * @inheritDoc
	 */
	public function verify(string $password, string $hash): bool {
		[$hash, $salt] = explode(':', $hash, 2);
		
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
		$hash = '';
		if ($this->enableSalting) {
			if ($this->saltPosition === 'b') {
				$hash .= $salt;
			}
			
			if ($this->encryptBeforeSalting) {
				$hash .= $this->encrypt($password);
			}
			else {
				$hash .= $password;
			}
			
			if ($this->saltPosition === 'a') {
				$hash .= $salt;
			}
			
			$hash = $this->encrypt($hash);
		}
		else {
			$hash = $this->encrypt($password);
		}
		
		return $this->encrypt($salt . $hash);
	}
	
	/**
	 * Encrypts a given string with the used encryption method.
	 */
	private function encrypt(string $string): string {
		switch ($this->encryptionMethod) {
			case 'c':
				return \crc32($string);
				break;
			
			case 'm':
				return \md5($string);
				break;
			
			case 's':
				return \sha1($string);
				break;
				
			default: 
				throw new \BadMethodCallException("Unknown encryption used");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function needsRehash(string $hash): bool {
		return false;
	}
}
