<?php
namespace wcf\data\user\menu\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.menu.item
 * @category	Community Framework
 */
class UserMenuItemEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\menu\item\UserMenuItem';
}
