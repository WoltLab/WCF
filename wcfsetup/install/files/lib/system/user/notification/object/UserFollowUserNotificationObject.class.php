<?php
namespace wcf\system\user\notification\object;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a following user as a notification object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object
 * @category	Community Framework
 */
class UserFollowUserNotificationObject extends DatabaseObjectDecorator implements IStackableUserNotificationObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\follow\UserFollow';
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getTitle()
	 */
	public function getTitle() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getURL()
	 */
	public function getURL() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IUserNotificationObject::getAuthorID()
	 */
	public function getAuthorID() {
		return $this->userID;
	}
	
	/**
	 * @see	\wcf\system\user\notification\object\IStackableUserNotificationObject::getRelatedObjectID()
	 */
	public function getRelatedObjectID() {
		return $this->followUserID; 
	}
}
