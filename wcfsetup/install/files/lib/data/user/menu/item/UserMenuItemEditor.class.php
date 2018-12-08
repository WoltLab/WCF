<?php
namespace wcf\data\user\menu\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Menu\Item
 * 
 * @method static	UserMenuItem	create(array $parameters = [])
 * @method		UserMenuItem	getDecoratedObject()
 * @mixin		UserMenuItem
 */
class UserMenuItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserMenuItem::class;
}
