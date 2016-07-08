<?php
namespace wcf\system\user\notification\event;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for profile comment likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 */
class UserProfileCommentLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.title.stacked', [
				'count' => $count,
				'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('wcf.user.notification.comment.like.title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		$owner = null;
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		}
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message.stacked', [
				'author' => $this->author,
				'authors' => $authors,
				'count' => $count,
				'others' => $count - 1,
				'owner' => $owner
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.like.message', [
			'author' => $this->author,
			'owner' => $owner
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		throw new \LogicException('Unreachable');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$owner = WCF::getUser();
		if ($this->additionalData['objectID'] != WCF::getUser()->userID) {
			$owner = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['objectID']);
		}
		
		return LinkHandler::getInstance()->getLink('User', ['object' => $owner], '#wall');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
}
