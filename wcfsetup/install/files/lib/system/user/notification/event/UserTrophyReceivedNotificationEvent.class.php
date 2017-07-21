<?php
namespace wcf\system\user\notification\event;

/**
 * Notification event for receiving a user trophy. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
class UserTrophyReceivedNotificationEvent extends AbstractUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('wcf.user.notification.com.woltlab.wcf.userTrophy.notification.received');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('wcf.user.notification.trophy.received.message', [
			'userTrophy' => $this->userNotificationObject,
			'author' => $this->author
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->userNotificationObject->getTrophy()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return $this->userNotificationObject->getDecoratedObject()->canSee();
	}
}
