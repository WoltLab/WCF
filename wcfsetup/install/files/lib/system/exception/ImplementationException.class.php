<?php
namespace wcf\system\exception;

/**
 * Exception implementation for cases when a class is expected to implement a certain
 * interface but that is not the case.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exception
 * @category	Community Framework
 * @since	2.2
 */
class ImplementationException extends \LogicException {
	/**
	 * ImplementationException constructor.
	 * 
	 * @param	string		$className
	 * @param	string		$interfaceName
	 */
	public function __construct($className, $interfaceName) {
		parent::__construct("Class '{$className}' does not implement interface '{$interfaceName}'.");
	}
}
