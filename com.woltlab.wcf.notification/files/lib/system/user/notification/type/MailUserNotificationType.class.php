<?php
namespace wcf\system\user\notification\type;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserEditor;
use wcf\data\user\User;
use wcf\system\mail\Mail;

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
class MailUserNotificationType implements UserNotificationType {        
	/**
	 * @see wcf\system\user\notification\type\UserNotificationType::send()
	 */
        public function send(UserNotification $notification, User $user, UserNotificationEvent $event) {
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
			$editor = new UserEditor($user);
			$editor->updateUserOptions(array('notificationMailToken' => $token));
		}
                $message .= "\n".$event->getLanguage()->getDynamicVariable('wcf.user.notification.type.mail.footer', array(
			'user' => $user,
			'pageURL' => FileUtil::addTrailingSlash(PAGE_URL),
			'token' => $token,
			'notification' => $notification
                ));

                // use short output as mail subject and strip its HTML
		$shortMessage = StringUtil::stripHTML($notification->shortOutput);

		// build mail
		$mail = new Mail(array($user->username => $user->email), $event->getLanguageVariable('wcf.user.notification.type.mail.subject', array('title' => $shortMessage)), $message);
                $mail->send();
        }

	/**
	 * @see wcf\system\user\notification\type\UserNotificationType::revoke()
	 */
        public function revoke(UserNotification $notification, User $user, UserNotificationEvent $event) {
        	// unsupported
        	return;
        }
}
