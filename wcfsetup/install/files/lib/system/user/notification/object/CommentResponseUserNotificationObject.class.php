<?php
namespace wcf\system\user\notification\object;
use wcf\data\DatabaseObjectDecorator;

/**
 * Notification object for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object
 * @category	Community Framework
 */
class CommentResponseUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\comment\response\CommentResponse';
	
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
}
