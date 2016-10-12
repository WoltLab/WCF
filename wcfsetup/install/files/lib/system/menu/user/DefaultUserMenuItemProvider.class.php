<?php
namespace wcf\system\menu\user;
use wcf\data\user\menu\item\UserMenuItem;
use wcf\data\DatabaseObjectDecorator;

/**
 * Default implementations of a user menu item provider.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User
 * 
 * @method	UserMenuItem	getDecoratedObject()
 * @mixin	UserMenuItem
 */
class DefaultUserMenuItemProvider extends DatabaseObjectDecorator implements IUserMenuItemProvider {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserMenuItem::class;
	
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		// explicit call to satisfy our interface
		return $this->getDecoratedObject()->getLink();
	}
}
