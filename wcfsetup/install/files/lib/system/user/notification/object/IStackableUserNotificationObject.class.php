<?php
namespace wcf\system\user\notification\object;

/**
 * This interface should be implemented by every object which supports stackable notifications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object
 * @category	Community Framework
 */
interface IStackableUserNotificationObject extends IUserNotificationObject {
	/**
	 * Returns the ID of the related object.
	 * 
	 * @return	integer
	 */
	public function getRelatedObjectID();
}
