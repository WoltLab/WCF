<?php
namespace wcf\system\user\notification\event;
use wcf\system\comment\CommentDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for profile commment likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::$stackable
	 */
	protected $stackable = true;
	
	/**
	 * @see	\wcf\system\user\notification\event\AbstractUserNotificationEvent::prepare()
	 */
	protected function prepare() {
		CommentDataHandler::getInstance()->cacheUserID($this->additionalData['objectID']);
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getTitle()
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.title.stacked', array(
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			));
		}
		
		return $this->getLanguage()->get('wcf.user.notification.comment.like.title');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getMessage()
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		$owner = null;
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = CommentDataHandler::getInstance()->getUser($this->additionalData['objectID']);
		}
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message.stacked', array(
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1,
				'owner' => $owner
			));
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message', array(
			'author' => $this->author,
			'owner' => $owner
		));
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') { /* not supported */ }
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getLink()
	 */
	public function getLink() {
		$owner = WCF::getUser();
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = CommentDataHandler::getInstance()->getUser($this->additionalData['objectID']);
		}
		
		return LinkHandler::getInstance()->getLink('User', array(
			'object' => $owner
		), '#wall');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEventHash()
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['objectID']);
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::supportsEmailNotification()
	 */
	public function supportsEmailNotification() {
		return false;
	}
}
