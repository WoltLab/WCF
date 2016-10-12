<?php
namespace wcf\system\option\user\group;
use wcf\system\option\IOptionType;

/**
 * Any group permission type should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
interface IUserGroupOptionType extends IOptionType {
	/**
	 * Returns the value which results by merging or null if nothing should be saved.
	 * 
	 * @param	mixed		$defaultValue
	 * @param	mixed		$groupValue
	 * @return	mixed
	 */
	public function merge($defaultValue, $groupValue);
}
