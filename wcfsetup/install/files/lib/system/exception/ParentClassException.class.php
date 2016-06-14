<?php
namespace wcf\system\exception;

/**
 * Exception implementation for cases when a class is expected to have a certain class
 * as a parent class but that is not the case.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @since	3.0
 */
class ParentClassException extends \LogicException {
	/**
	 * ImplementationException constructor.
	 *
	 * @param	string		$className
	 * @param	string		$parentClassName
	 */
	public function __construct($className, $parentClassName) {
		parent::__construct("Class '{$className}' does not extend class '{$parentClassName}'.");
	}
}
