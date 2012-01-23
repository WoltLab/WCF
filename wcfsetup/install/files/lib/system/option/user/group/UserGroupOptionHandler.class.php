<?php
namespace wcf\system\option\user\group;
use wcf\system\exception\SystemException;
use wcf\system\option\OptionHandler;
use wcf\util\ClassUtil;

/**
 * Handles user group options.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class UserGroupOptionHandler extends OptionHandler {
	/**
	 * @see	wcf\system\option\OptionHandler::getClassName()
	 */
	protected function getClassName($type) {
		$className = parent::getClassName($type);
		
		if ($className === null) {
			$className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType';
			
			// validate class
			if (!class_exists($className)) {
				return null;
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\IOptionType");
			}
		}
		
		return $className;
	}
}