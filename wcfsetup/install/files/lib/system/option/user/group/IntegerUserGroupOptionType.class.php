<?php
namespace wcf\system\option\user\group;
use wcf\system\option\IntegerOptionType;

/**
 * User group option type implementation for integer input fields.
 * 
 * The merge of option values returns the highest value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class IntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType {
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		if ($groupValue > $defaultValue) {
			return $groupValue;
		}
		
		return null;
	}
}
