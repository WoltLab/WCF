<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObjectEditor;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Provides functions to edit user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification
 * 
 * @method static	UserNotification	create(array $parameters = [])
 * @method		UserNotification	getDecoratedObject()
 * @mixin		UserNotification
 */
class UserNotificationEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserNotification::class;
	
	/**
	 * Marks this notification as confirmed.
	 *
	 * @deprecated	5.2 This method currently only is a wrapper for markAsConfirmedByIDs in UserNotificationHandler.
	 * 		Please use \wcf\system\user\notification\UserNotificationHandler::markAsConfirmedByIDs()
	 * 		from now on, as this method may be removed in the future.
	 */
	public function markAsConfirmed() {
		UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$this->notificationID]);
	}
}
