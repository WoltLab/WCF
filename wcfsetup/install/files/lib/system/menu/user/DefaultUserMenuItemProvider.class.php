<?php
namespace wcf\system\menu\user;
use wcf\data\DatabaseObjectDecorator;

/**
 * Default implementations of a user menu item provider.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user
 * @category	Community Framework
 */
class DefaultUserMenuItemProvider extends DatabaseObjectDecorator implements IUserMenuItemProvider {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\menu\item\UserMenuItem';
	
	/**
	 * @see	\wcf\system\menu\page\IUserMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\menu\page\IUserMenuItemProvider::getLink()
	 */
	public function getLink() {
		// explicit call to satisfy our interface
		return $this->getDecoratedObject()->getLink();
	}
}
