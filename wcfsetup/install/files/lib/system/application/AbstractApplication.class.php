<?php
namespace wcf\system\application;

/**
 * Default implementation for all applications for the community framework.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category 	Community Framework
 */
abstract class AbstractApplication implements IApplication {
	/**
	 * @see	wcf\system\application\IApplication::__callStatic()
	 */
	public static function __callStatic($method, array $arguments) {
		return call_user_func_array(array('wcf\system\WCF', $method), $arguments);
	}
}
