<?php
namespace wcf\system\menu\user\profile\content;

/**
 * Default interface for user profile menu content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user.profile.content
 * @category	Community Framework
 */
interface IUserProfileMenuContent {
	/**
	 * Returns content for this user profile menu item.
	 * 
	 * @param	integer		$userID
	 * @return	string
	 */
	public function getContent($userID);
	
	/**
	 * Returns true if the associated menu item should be visible for the active user.
	 * 
	 * @param	integer		$userID
	 * @return	boolean
	 */
	public function isVisible($userID);
}
