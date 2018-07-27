<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit usergroup option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Option\Category
 * 
 * @method static	UserGroupOptionCategory		create(array $parameters = [])
 * @method		UserGroupOptionCategory		getDecoratedObject()
 * @mixin		UserGroupOptionCategory
 */
class UserGroupOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserGroupOptionCategory::class;
}
