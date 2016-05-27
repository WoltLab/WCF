<?php
namespace wcf\system\user\notification\event;
use wcf\system\request\LinkHandler;

/**
 * Notification event for followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserFollowFollowingUserNotificationEvent extends AbstractUserNotificationEvent {
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
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.mail.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1,
				'notificationType' => $notificationType
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.mail', ['author' => $this->author]);
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
		return sha1($this->eventID . '-' . $this->userNotificationObject->followUserID);
	}
}
