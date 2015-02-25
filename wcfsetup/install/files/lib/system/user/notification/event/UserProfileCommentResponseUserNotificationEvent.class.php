<?php
namespace wcf\system\user\notification\event;
use wcf\system\comment\CommentDataHandler;
use wcf\system\request\LinkHandler;

/**
 * User notification event for profile commment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentResponseUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::$stackable
	 */
	protected $stackable = true;
	
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::prepare()
	 */
	protected function prepare() {
		CommentDataHandler::getInstance()->cacheCommentID($this->userNotificationObject->commentID);
		CommentDataHandler::getInstance()->cacheUserID($this->additionalData['objectID']);
		CommentDataHandler::getInstance()->cacheUserID($this->additionalData['userID']);
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.title.stacked', array(
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			));
		}
		
		return $this->getLanguage()->get('wcf.user.notification.commentResponse.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$comment = CommentDataHandler::getInstance()->getComment($this->userNotificationObject->commentID);
		$owner = CommentDataHandler::getInstance()->getUser($comment->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.message.stacked', array(
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'owner' => $owner,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.message', array(
			'author' => $this->author,
			'owner' => $owner
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentDataHandler::getInstance()->getComment($this->userNotificationObject->commentID);
		$owner = CommentDataHandler::getInstance()->getUser($comment->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.mail.stacked', array(
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'notificationType' => $notificationType,
				'others' => $count - 1,
				'owner' => $owner,
				'response' => $this->userNotificationObject,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponse.mail', array(
			'response' => $this->userNotificationObject,
			'author' => $this->author,
			'owner' => $owner,
			'notificationType' => $notificationType
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getLink()
	 */
	public function getLink() {
		$comment = CommentDataHandler::getInstance()->getComment($this->userNotificationObject->commentID);
		$user = CommentDataHandler::getInstance()->getUser($comment->objectID);
		
		return LinkHandler::getInstance()->getLink('User', array('object' => $user), '#wall');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEventHash()
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->userNotificationObject->commentID);
	}
}
