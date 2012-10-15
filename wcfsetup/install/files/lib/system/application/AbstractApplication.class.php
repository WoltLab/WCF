<?php
namespace wcf\system\application;
use wcf\system\SingletonFactory;

/**
 * Default implementation for all applications for community framework.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category	Community Framework
 */
abstract class AbstractApplication extends SingletonFactory implements IApplication {
	/**
	 * @see	wcf\system\application\IApplication::__callStatic()
	 */
	public static function __callStatic($method, array $arguments) {
		return call_user_func_array(array('wcf\system\WCF', $method), $arguments);
	}
}
