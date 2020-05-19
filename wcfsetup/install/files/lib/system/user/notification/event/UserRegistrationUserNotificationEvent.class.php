<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\UserProfile;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;
use wcf\system\user\notification\object\UserRegistrationUserNotificationObject;

/**
 * Notification event for new user registrations.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since       5.2
 *
 * @method	UserRegistrationUserNotificationObject	getUserNotificationObject()
 */
class UserRegistrationUserNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent {
	use TTestableUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.userRegistration.title.stacked', ['count' => $count]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.userRegistration.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.userRegistration.message.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.userRegistration.message', ['author' => $this->author]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
			'template' => 'email_notification_userRegistration',
			'application' => 'wcf'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->author->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID);
	}
	
	/**
	 * @inheritDoc
	 * @return	UserFollowUserNotificationObject[]
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		return [new UserRegistrationUserNotificationObject($author->getDecoratedObject())];
	}
}
