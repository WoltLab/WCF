<?php
namespace wcf\system\user\notification\object;
use wcf\data\user\follow\UserFollow;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a following user as a notification object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object
 * @category	Community Framework
 * 
 * @method	UserFollow	getDecoratedObject()
 * @mixin	UserFollow
 */
class UserFollowUserNotificationObject extends DatabaseObjectDecorator implements IStackableUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserFollow::class;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRelatedObjectID() {
		return $this->followUserID; 
	}
}
