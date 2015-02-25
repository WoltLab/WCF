<?php
namespace wcf\system\menu\page;
use wcf\data\IDatabaseObjectProcessor;

/**
 * Any page menu item provider should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.page
 * @category	Community Framework
 */
interface IPageMenuItemProvider extends IDatabaseObjectProcessor {
	/**
	 * Returns true if the associated menu item should be visible for the active user.
	 * 
	 * @return	boolean
	 */
	public function isVisible();
	
	/**
	 * Returns the number of notifications for the associated menu item.
	 * 
	 * @return	integer
	 */
	public function getNotifications();
	
	/**
	 * Returns the href of the associated menu item.
	 * 
	 * @return	string
	 */
	public function getLink();
}
