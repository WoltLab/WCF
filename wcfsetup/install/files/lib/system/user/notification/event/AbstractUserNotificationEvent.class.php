<?php
namespace wcf\system\user\notification\event;
use wcf\data\language\Language;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Provides a default implementation for user notification events.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2015 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.event
 * @category	Community Framework
 */
abstract class AbstractUserNotificationEvent extends DatabaseObjectDecorator implements IUserNotificationEvent {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\event\UserNotificationEvent';
	
	/**
	 * author object
	 * @var	\wcf\data\user\UserProfile
	 */
	protected $author = null;
	
	/**
	 * list of authors for stacked notifications
	 * @var	array<\wcf\data\user\UserProfile>
	 */
	protected $authors = array();
	
	/**
	 * notification stacking support
	 * @var	boolean
	 */
	protected $stackable = false;
	
	/**
	 * user notification
	 * @var	\wcf\data\user\notification\UserNotification
	 */
	protected $notification = null;
	
	/**
	 * user notification object
	 * @var	\wcf\system\user\notification\object\IUserNotificationObject
	 */
	protected $userNotificationObject = null;
	
	/**
	 * additional data for this event
	 * @var	array<mixed>
	 */
	protected $additionalData = array();
	
	/**
	 * language object
	 * @var	\wcf\data\language\Language
	 */
	protected $language = null;
	
	/**
	 * list of point of times for each period's end
	 * @var	array<string>
	 */
	protected static $periods = array();
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::setAuthors()
	 */
	public function setAuthors(array $authors) {
		$this->authors = $authors;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::setObject()
	 */
	public function setObject(UserNotification $notification, IUserNotificationObject $object, UserProfile $author, array $additionalData = array()) {
		$this->notification = $notification;
		$this->userNotificationObject = $object;
		$this->author = $author;
		$this->additionalData = $additionalData;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getAuthorID()
	 */
	public function getAuthorID() {
		return $this->author->userID;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getAuthor()
	 */
	public function getAuthor() {
		return $this->author;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getAuthors()
	 */
	public function getAuthors() {
		return $this->authors;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::isVisible()
	 */
	public function isVisible() {
		if ($this->options) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($this->options));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
			if (!$hasEnabledOption) return false;
		}
		
		$hasPermission = true;
		if ($this->permissions) {
			$hasPermission = false;
			$permissions = explode(',', $this->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
				break;
				}
			}
		}
		if (!$hasPermission) return false;
		return true;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailTitle()
	 */
	public function getEmailTitle() {
		return $this->getTitle();
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEmailMessage()
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return $this->getMessage();
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getEventHash()
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->userNotificationObject->getObjectID());
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::setLanguage()
	 */
	public function setLanguage(Language $language) {
		$this->language = $language;
	}
	
	/**
	 * Gets the language of this event.
	 * 
	 * @return	\wcf\data\language\Language
	 */
	public function getLanguage() {
		if ($this->language !== null) return $this->language;
		return WCF::getLanguage();
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::isStackable()
	 */
	public function isStackable() {
		return $this->stackable;
	}
	
	/**
	 * Returns the readable period matching this notification.
	 * 
	 * @return	string
	 */
	public function getPeriod() {
		if (empty(self::$periods)) {
			$date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$date->setTimezone(WCF::getUser()->getTimeZone());
			$date->setTime(0, 0, 0);
			
			self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.today');
			
			// 1 day back
			$date->modify('-1 day');
			self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.yesterday');
			
			// 2-6 days back
			for ($i = 0; $i < 6; $i++) {
				$date->modify('-1 day');
				self::$periods[$date->getTimestamp()] = DateUtil::format($date, 'l');
			}
		}
		
		foreach (self::$periods as $time => $period) {
			if ($this->notification->time >= $time) {
				return $period;
			}
		}
		
		return WCF::getLanguage()->get('wcf.date.period.older');
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::supportsEmailNotification()
	 */
	public function supportsEmailNotification() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::checkAccess()
	 */
	public function checkAccess() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::deleteNoAccessNotification()
	 */
	public function deleteNoAccessNotification() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::isConfirmed()
	 */
	public function isConfirmed() {
		return ($this->notification->confirmTime > 0);
	}
	
	/**
	 * @see	\wcf\system\user\notification\event\IUserNotificationEvent::getNotification()
	 */
	public function getNotification() {
		return $this->notification;
	}
}
