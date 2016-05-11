<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit usergroup option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 * 
 * @method	UserGroupOptionCategory		getDecoratedObject()
 * @mixin	UserGroupOptionCategory
 */
class UserGroupOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserGroupOptionCategory::class;
}
