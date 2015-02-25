<?php
namespace wcf\action;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Marks target notification as confirmed and forwards to the notification URL.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class NotificationConfirmAction extends AbstractAction {
	/**
	 * @see	\wcf\action\AbstractAction::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * user notification object
	 * @var	\wcf\data\user\notification\UserNotification
	 */
	public $notification = null;
	
	/**
	 * user notification id
	 * @var	integer
	 */
	public $notificationID = 0;
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->notificationID = intval($_REQUEST['id']);
		
		$this->notification = new UserNotification($this->notificationID);
		if (!$this->notification->notificationID) {
			throw new IllegalLinkException();
		}
		
		if ($this->notification->userID != WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->notification->confirmTime) {
			UserNotificationHandler::getInstance()->markAsConfirmedByID($this->notification->notificationID);
		}
		
		$event = new UserNotificationEvent($this->notification->eventID);
		$objectType = ObjectTypeCache::getInstance()->getObjectType($event->objectTypeID);
		$objects = $objectType->getProcessor()->getObjectsByIDs(array($this->notification->objectID));
		
		$userProfile = null;
		if ($this->notification->authorID) {
			$userProfile = new UserProfile(new User($this->notification->authorID));
		}
		else {
			$userProfile = new UserProfile(new User(null, array('userID' => null, 'username' => WCF::getLanguage()->get('wcf.user.guest'))));
		}
		
		$className = $event->className;
		$notificationEvent = new $className($event);
		$notificationEvent->setObject(
			$this->notification,
			$objects[$this->notification->objectID],
			$userProfile,
			$this->notification->additionalData
		);
		
		HeaderUtil::redirect($notificationEvent->getLink());
		exit;
	}
}
