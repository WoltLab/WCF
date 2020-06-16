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
	 * @deprecated 5.2 Use `UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$notificationID]);` instead.
	 */
	public function markAsConfirmed() {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$this->notificationID]);
	}
}
