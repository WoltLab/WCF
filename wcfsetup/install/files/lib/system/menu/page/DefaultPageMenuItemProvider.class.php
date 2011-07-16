<?php
namespace wcf\system\menu\page;

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
class DefaultPageMenuItemProvider implements PageMenuItemProvider {
	/**
	 * @see wcf\system\menu\page\PageMenuItemProvider::isVisible()
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * @see wcf\system\menu\page\PageMenuItemProvider::getNotifications()
	 */
	public function getNotifications() {
		return 0;
	}
}
