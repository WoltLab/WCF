<?php
namespace wcf\system\menu\page;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides default implementations for page menu item providers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.page
 * @category 	Community Framework
 */
class DefaultPageMenuItemProvider extends DatabaseObjectDecorator implements IPageMenuItemProvider {
	/**
	 * @see wcf\system\menu\page\IPageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @see wcf\system\menu\page\IPageMenuItemProvider::getNotifications()
	 */
	public function getNotifications() {
		return 0;
	}
}
