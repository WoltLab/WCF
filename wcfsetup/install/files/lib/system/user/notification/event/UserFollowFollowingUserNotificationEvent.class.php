<?php
namespace wcf\system\user\notification\event;
use wcf\system\request\LinkHandler;

/**
 * Notification event for followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserFollowFollowingUserNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::$stackable
	 */
	protected $stackable = true;
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.title.stacked', array('count' => $count));
		}
		
		return $this->getLanguage()->get('wcf.user.notification.follow.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.message.stacked', array(
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.message', array('author' => $this->author));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.mail.stacked', array(
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1,
				'notificationType' => $notificationType
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.mail', array('author' => $this->author));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', array('object' => $this->author));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEventHash()
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->userNotificationObject->followUserID);
	}
}
