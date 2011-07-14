<?php
namespace wcf\system\menu\page;

/**
 * Any page menu item provider should implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.page
 * @category 	Community Framework
 */
interface PageMenuItemProvider {
	/**
	 * Returns true if the associated menu item should be visible for the active user.
	 * 
	 * @return boolean
	 */
	public function isVisible();
	
	/**
	 * Returns the number of notifications for the associated menu item.
	 * 
	 * @return boolean
	 */
	public function getNotifications();
}
?>