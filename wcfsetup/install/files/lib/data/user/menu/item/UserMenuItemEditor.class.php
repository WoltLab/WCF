<?php
namespace wcf\data\user\menu\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Menu\Item
 * 
 * @method	UserMenuItem	getDecoratedObject()
 * @mixin	UserMenuItem
 */
class UserMenuItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserMenuItem::class;
}
