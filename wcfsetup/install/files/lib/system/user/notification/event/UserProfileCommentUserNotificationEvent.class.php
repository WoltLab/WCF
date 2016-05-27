<?php
namespace wcf\system\user\notification\event;
use wcf\data\user\User;
use wcf\system\request\LinkHandler;

/**
 * User notification event for profile commments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
class UserProfileCommentUserNotificationEvent extends AbstractUserNotificationEvent {
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
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.comment.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.message', [
			'author' => $this->author
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		$user = new User($this->userNotificationObject->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.mail.stacked', [
				'author' => $this->author,
				'authors' => array_values($authors),
				'count' => $count,
				'others' => $count - 1,
				'owner' => $user,
				'notificationType' => $notificationType,
				'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.mail', [
			'comment' => $this->userNotificationObject,
			'author' => $this->author,
			'owner' => $user,
			'notificationType' => $notificationType
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('User', ['id' => $this->userNotificationObject->objectID], '#wall');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->notification->userID);
	}
}
