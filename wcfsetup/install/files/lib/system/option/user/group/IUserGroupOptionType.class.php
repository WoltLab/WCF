<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\option\IOptionType;

/**
 * Any group permission type should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
interface IUserGroupOptionType extends IOptionType {
	/**
	 * Merges the different values of an option to a single value.
	 * 
	 * @param	array		$values
	 * @return	mixed
	 */
	public function merge(array $values);
	
	/**
	 * Checks if the User has given a value "higher" value than he has
	 * 
	 * @param	wcf\data\option\Option		$option
	 * @param	string						$newValue
	 */
	public function checkPermissions(Option $option, $newValue);
}
