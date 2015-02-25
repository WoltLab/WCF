<?php
namespace wcf\system\user\notification\event;
use wcf\data\comment\Comment;
use wcf\data\user\User;
use wcf\system\comment\CommentDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for profile's owner for commment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::$stackable
	 */
	protected $stackable = true;
	
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::prepare()
	 */
	protected function prepare() {
		CommentDataHandler::getInstance()->cacheCommentID($this->userNotificationObject->commentID);
		CommentDataHandler::getInstance()->cacheUserID($this->additionalData['userID']);
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.title.stacked', array(
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			));
		}
		
		return $this->getLanguage()->get('wcf.user.notification.commentResponseOwner.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$comment = CommentDataHandler::getInstance()->getComment($this->userNotificationObject->commentID);
		if ($comment->userID) {
			$commentAuthor = CommentDataHandler::getInstance()->getUser($comment->userID);
		}
		else {
			$commentAuthor = new User(null, array(
				'username' => $comment->username
			));
		}
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.message.stacked', array(
				'author' => $commentAuthor,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.message', array(
			'author' => $this->author,
			'commentAuthor' => $commentAuthor
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$comment = new Comment($this->userNotificationObject->commentID);
		$owner = new User($comment->objectID);
		if ($comment->userID) {
			$commentAuthor = new User($comment->userID);
		}
		else {
			$commentAuthor = new User(null, array(
				'username' => $comment->username
			));
		}
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.mail.stacked', array(
				'author' => $this->author,
				'authors' => array_values($authors),
				'commentAuthor' => $commentAuthor,
				'count' => $count,
				'notificationType' => $notificationType,
				'others' => $count - 1,
				'owner' => $owner,
				'response' => $this->userNotificationObject,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.commentResponseOwner.mail', array(
			'response' => $this->userNotificationObject,
			'author' => $this->author,
			'commentAuthor' => $commentAuthor,
			'owner' => $owner,
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
		return sha1($this->eventID . '-' . $this->userNotificationObject->commentID);
	}
}
