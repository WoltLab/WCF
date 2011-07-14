<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/type/UserNotificationReceivableType.class.php');
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');

/**
 * A notification type for sending mail notifications.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.type
 * @category 	Community Framework
 */
class MailUserNotificationReceivableType implements UserNotificationReceivableType {
	/**
	 * @see UserNotificationReceivableType::send()
	 */
	public function send(User $user, UserNotificationConveyableEvent $event, UserNotificationEditor $notification) {
		// get messages
		$message = $event->getMessage($this, array(
			'user' => $user,
			'pageURL' => FileUtil::addTrailingSlash(PAGE_URL)
		));

		// append notification mail footer
		$token = $user->notificationMailToken;
		if (!$token) {
			// generate token if not present
			$token = StringUtil::substring(StringUtil::getHash(serialize(array($user->userID, StringUtil::getRandomID()))), 0, 20);
			$editor = $user->getEditor();
			$editor->updateOptions(array('notificationMailToken' => $token));
		}
		$message .= '\n'.$event->getLanguage()->getDynamicVariable('wcf.user.notification.type.mail.footer',  array(
			'user' => $user,
			'pageURL' => FileUtil::addTrailingSlash(PAGE_URL),
			'token' => $token,
			'notification' => $notification
		));

		// Use short output as mail subject and strip its HTML
		$shortMessage = StringUtil::stripHTML($notification->shortOutput);

		// build mail
		$mail = new Mail(array($user->username => $user->email), $event->getLanguageVariable('wcf.user.notification.type.mail.subject', array('title' => $shortMessage)), $message);
		$mail->send();
	}

	/**
	 * @see UserNotificationReceivableType::revoke()
	 */
	public function send(User $user, UserNotificationConveyableEvent $event, UserNotificationEditor $notification) {
		// unsupported
		return;
	}

	/**
	 * @see UserNotificationReceivableType::getName()
	 */
	public function getName() {
		return 'mail';
	}

	/**
	 * @see UserNotificationReceivableType::getIcon()
	 */
	public function getIcon() {
		return 'email';
	}
}
?>