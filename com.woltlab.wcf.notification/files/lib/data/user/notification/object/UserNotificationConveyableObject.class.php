<?php
/**
 * This interface should be implemented by every object which is part of a notification
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
interface UserNotificationConveyableObject {
	/**
	 * Returns the ID of this object.
	 *
	 * @return	string
	 */
	public function getObjectID();

	/**
	 * Returns the title of this object.
	 *
	 * @return	string
	 */
	public function getTitle();

	/**
	 * Returns the url of this object.
	 *
	 * @return	string
	 */
	public function getURL();

	/**
	 * Returns the icon of this object.
	 *
	 * @return	string
	 */
	public function getIcon();
}
?>