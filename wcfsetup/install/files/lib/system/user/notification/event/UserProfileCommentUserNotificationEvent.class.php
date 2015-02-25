<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\User;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;
use wcf\system\WCF;

/**
 * User notification event for profile commments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentUserNotificationEvent extends AbstractUserNotificationEvent {
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
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.title.stacked', array(
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			));
		}
		
		return $this->getLanguage()->get('wcf.user.notification.comment.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message.stacked', array(
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message', array(
			'author' => $this->author
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$user = new User($this->userNotificationObject->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.mail.stacked', array(
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'owner' => $user,
				'notificationType' => $notificationType,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.mail', array(
			'comment' => $this->userNotificationObject,
			'author' => $this->author,
			'owner' => $user,
			'notificationType' => $notificationType
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getLink()
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', array('object' => WCF::getUser()), '#wall');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEventHash()
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->notification->userID);
	}
}
