<?php
namespace wcf\data\user\group\option;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;

/**
 * Provides functions to edit usergroup options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option
 * @category	Community Framework
 * 
 * @method	UserGroupOption		getDecoratedObject()
 * @mixin	UserGroupOption
 */
class UserGroupOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserGroupOption::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		UserGroupOptionCacheBuilder::getInstance()->reset();
	}
}
