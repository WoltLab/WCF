<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowAction;
use wcf\data\user\UserProfile;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;

/**
 * Notification event for followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * 
 * @method	UserFollowUserNotificationObject	getUserNotificationObject()
 */
class UserFollowFollowingUserNotificationEvent extends AbstractUserNotificationEvent implements ITestableUserNotificationEvent {
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
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.title.stacked', ['count' => $count]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.follow.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.message.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.message', ['author' => $this->author]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
			'template' => 'email_notification_userFollowFollowing',
			'application' => 'wcf'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', ['object' => $this->author]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->followUserID);
	}
	
	/**
	 * @inheritDoc
	 * @return	UserFollowUserNotificationObject[]
	 * @since	3.1
	 */
	public static function getTestObjects(UserProfile $recipient, UserProfile $author) {
		$follow = UserFollow::getFollow($recipient->userID, $author->userID);
		if (!$follow->followID) {
			$follow = (new UserFollowAction([], 'create', [
				'data' => [
					'userID' => $recipient->userID,
					'followUserID' => $author->userID,
					'time' => TIME_NOW - 60 * 60
				]
			]))->executeAction()['returnValues'];
		}
		
		return [new UserFollowUserNotificationObject($follow)];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.1
	 */
	public static function getTestAdditionalData(IUserNotificationObject $object) {
		/** @var UserFollowUserNotificationObject $object */
		
		return [$object->followUserID];
	}
}
