<?php
namespace wcf\system\option\user\group;
use wcf\system\option\FileSizeOptionType;

/**
 * FileSizeUserGroupOptionType is an implementation of IUserGroupOptionType for file sizes.
 * The merge of option values returns the highest value.
 * 
 * @author	Tim Düsterhus
 * @copyright	2011 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user.group
 * @category 	Community Framework
 */
class FileSizeUserGroupOptionType extends FileSizeOptionType implements IUserGroupOptionType {
	/**
	 * @see wcf\system\option\user.group\IUserGroupOptionType::merge()
	 */
	public function merge(array $values) {
		return max($values);
	}
}
