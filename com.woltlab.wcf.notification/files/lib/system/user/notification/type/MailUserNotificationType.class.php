<?php
namespace wcf\system\user\notification\type;
use wcf\data\user\notification\recipient\UserNotificationRecipient;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserEditor;
use wcf\system\mail\Mail;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * A notification type for sending mail notifications.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.type
 * @category 	Community Framework
 */
class MailUserNotificationType extends AbstractUserNotificationType {        
	/**
	 * @see wcf\system\user\notification\type\IUserNotificationType::send()
	 */
        public function send(UserNotification $notification, UserNotificationRecipient $user, IUserNotificationEvent $event) {
                // get message
		$message = $event->getMessage($this, array(
			'user' => $user,
			'pageURL' => FileUtil::addTrailingSlash(PAGE_URL)
                ));

                // append notification mail footer
		$token = $user->notificationMailToken;
		if (!$token) {
			// generate token if not present
			$token = StringUtil::substring($token = StringUtil::getHash(serialize(array($user->userID, StringUtil::getRandomID()))), 0, 20);
			$editor = new UserEditor($user->getDecoratedObject());
			$editor->updateUserOptions(array('notificationMailToken' => $token));
		}
                $message .= "\n".$user->getLanguage()->getDynamicVariable('wcf.user.notification.type.mail.footer', array(
			'user' => $user,
			'pageURL' => FileUtil::addTrailingSlash(PAGE_URL),
			'token' => $token,
			'notification' => $notification
                ));

                // use short output as mail subject and strip its HTML
		$shortMessage = StringUtil::stripHTML($notification->shortOutput);

		// build mail
		$mail = new Mail(array($user->username => $user->email), $user->getLanguage()->getDynamicVariable('wcf.user.notification.type.mail.subject', array('title' => $shortMessage)), $message);
                $mail->send();
        }

	/**
	 * @see wcf\system\user\notification\type\IUserNotificationType::revoke()
	 */
        public function revoke(UserNotification $notification, UserNotificationRecipient $user, IUserNotificationEvent $event) {
        	// unsupported
        	return;
        }
}
