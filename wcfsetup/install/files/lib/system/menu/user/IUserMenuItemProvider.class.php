<?php
namespace wcf\system\menu\user;
use wcf\data\IDatabaseObjectProcessor;

/**
 * Any user menu item provider should implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user
 * @category	Community Framework
 */
interface IUserMenuItemProvider extends IDatabaseObjectProcessor {
	/**
	 * Returns true if the associated menu item should be visible for the active user.
	 * 
	 * @return	boolean
	 */
	public function isVisible();
	
	/**
	 * Returns the href of the associated menu item.
	 * 
	 * @return	string
	 */
	public function getLink();
}
