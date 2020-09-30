<?php
namespace wcf\system\user\authentication\password;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
use wcf\system\user\authentication\password\algorithm\Bcrypt;
use wcf\system\user\authentication\password\algorithm\Wcf1e;

/**
 * Handles loading of password algorithms.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication\Password
 * @since	5.4
 */
final class PasswordAlgorithmManager extends SingletonFactory {
	/**
	 * Returns the password algorithm with the given name.
	 *
	 * @throws InvalidArgumentException If the password algorithm does not exist.
	 * @throws ImplementationException If the password algorithm does not implement IPasswordAlgorithm.
	 */
	public function getAlgorithmFromName(string $name): IPasswordAlgorithm {
		// The wcf1e algorithm can be recognized with the following regular expression.
		// The algorithm is handled by the Wcf1e password algorithm class. 
		if (preg_match('~^wcf1e[cms][01][ab][01]$~', $name)) {
			return new Wcf1e($name);
		}
		
		$className = 'wcf\system\user\authentication\password\algorithm\\'.\ucfirst($name);
		
		if (!\class_exists($className)) {
			throw new \InvalidArgumentException("Unknown password algorithm '$name'.");
		}
		
		if (!\is_subclass_of($className, IPasswordAlgorithm::class)) {
			throw new ImplementationException($className, IPasswordAlgorithm::class);
		}
		
		return new $className();
	}
	
	/**
	 * Returns the short name for the given password algorithm.
	 */
	public function getNameFromAlgorithm(IPasswordAlgorithm $algorithm): string {
		$name = \get_class($algorithm);

		// Strip namespace.
		$name = \preg_replace('/^\\\\?wcf\\\\system\\\\user\\\\authentication\\\\password\\\\algorithm\\\\/', '', $name);
		
		return $name;
	}
	
	/**
	 * Returns the default password algorithm.
	 */
	public function getDefaultAlgorithm(): IPasswordAlgorithm {
		return new Bcrypt();
	}
}
