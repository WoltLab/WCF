<?php
// wcf import
require_once(WCF_DIR.'lib/data/user/notification/event/UserNotificationConveyableEvent.class.php');
require_once(WCF_DIR.'lib/data/user/notification/event/UserNotificationEvent.class.php');
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationConveyableObject.class.php');
require_once(WCF_DIR.'lib/data/user/notification/UserNotification.class.php');

/**
 * This is the default and simple notification event class if you don't need further customization
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2010 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.event
 * @category 	Community Framework
 */
class DefaultUserNotificationConveyableEvent implements UserNotificationConveyableEvent {
	/**
	 * notification event
	 * 
	 * @var UserNotificationEvent
	 */
	protected $event = null;
	
	/**
	 * The language object of the recipient user's language
	 *
	 * @var Language
	 */
	protected $language = null;

	/**
	 * The notification object
	 *
	 * @var UserNotificationConveyableObject
	 */
	protected $object = null;

	/**
	 * Additional data for this event
	 *
	 * @var array<mixed>
	 */
	public $additionalData = array();

	/**
	 * Creates a new DefaultUserNotificationConveyableEvent object.
	 * 
	 * @param	UserNotificationEvent		$event 
	 */
	public function __construct(UserNotificationEvent $event) {
		$this->event = $event;
	}
	
	/**
	 * @see UserNotificationConveyableEvent::initialize()
	 */
	public function initialize(&$data) {
		return;
	}

	/**
	 * @see UserNotificationConveyableEvent::getMessage()
	 */
	public function getMessage(NotificationType $notificationType, $additionalVariables = array()) {
		return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName().'.'.$notificationType->getName(), $additionalVariables);
	}

	/**
	 * @see UserNotificationConveyableEvent::getShortOutput()
	 */
	public function getShortOutput() {
		 return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName().'.short');
	}

	/**
	 * @see UserNotificationConveyableEvent::getMediumOutput()
	 */
	public function getMediumOutput() {
		 return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName().'.medium');
	}

	/**
	 * @see UserNotificationConveyableEvent::getShortOutput()
	 */
	public function getOutput() {
		return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName());
	}

	/**
	 * @see UserNotificationConveyableEvent::getTitle()
	 */
	public function getTitle() {
		 return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName().'.title');
	}

	/**
	 * @see UserNotificationConveyableEvent::getDescription()
	 */
	public function getDescription() {
		 return $this->getLanguageVariable($this->event->languageCategory.'.'.$this->getEventName().'.description');
	}

	/**
	 * @see UserNotificationConveyableEvent::getLanguageVariable()
	 */
	public function getLanguageVariable($var, $additionalVariables = array()) {
		return $this->getLanguage()->getDynamicVariable($var, array_merge($additionalVariables,
			 array(
				'event' => $this
			 )
		 ));
	}

	/**
	 * @see UserNotificationConveyableEvent::supportsNotificationType()
	 */
	public function supportsNotificationType(NotificationType $notificationType) {
		// returns true if the language variable exists. By using the
		// static getter, errors are avoided
		return ($this->getLanguage()->get($this->event->languageCategory.'.'.$this->getEventName().'.'.$notificationType->getName()) != $this->event->languageCategory.'.'.$this->getEventName().'.'.$notificationType->getName());
	}

	/**
	 * @see UserNotificationConveyableEvent::getLanguage()
	 */
	public function getLanguage() {
		if ($this->language === null || !$this->language instanceof Language) {
			$this->language = WCF::getLanguage();
		}

		return $this->language;
	}

	/**
	 * @see UserNotificationConveyableEvent::setLanguage()
	 */
	public function setLanguage(Language $language) {
		$this->language = $language;
	}

	/**
	 * @see UserNotificationConveyableEvent::getObject()
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * @see UserNotificationConveyableEvent::setObject()
	 */
	public function setObject(UserNotificationConveyableObject $object, $additionalData = array()) {
		$this->object = $object;
		$this->additionalData = $additionalData;
	}

	/**
	 * @see UserNotificationConveyableEvent::getEventName()
	 */
	public function getEventName() {
		return $this->event->eventName;
	}

	/**
	 * @see UserNotificationConveyableEvent::getIcon()
	 */
	public function getIcon() {
		return $this->event->icon;
	}

	/**
	 * @see UserNotificationConveyableEvent::getAcceptURL()
	 */
	public function getAcceptURL(UserNotification $notification) {
		if ($this->event->requiresConfirmation) {
			if ($this->event->acceptURL) {
				return $this->parseURL($this->event->acceptURL, $notification);
			}
			else {
				return $this->parseURL('index.php?action=NotificationConfirm&notificationID='.$notification->notificationID, $notification);
			}
		}
		else {
			return '';
		}
	}

	/**
	 * @see UserNotificationConveyableEvent::getDeclineURL()
	 */
	public function getDeclineURL(UserNotification $notification) {
		if ($this->event->requiresConfirmation && $this->event->declineURL) {
			return $this->parseURL($this->event->declineURL, $notification);
		}
		else {
			return '';
		}
	}

	/**
	 * Parses accept and decline URLs
	 *
	 * @param       string			$url
	 * @param       UserNotification	$notification
	 * @return      string
	 */
	protected function parseURL($url, UserNotification $notification) {
		$url = str_replace('%u', $notification->userID, $url);
		$url = str_replace('%o', $notification->objectID, $url);
		$url = str_replace('%n', $notification->notificationID, $url);

		// append request uri
		if (WCF::getSession()->requestMethod == 'GET') {
			if (StringUtil::indexOf($url, '?') !== false) {
				$url .= '&';
			}
			else {
				$url .= '?';
			}
			preg_match('/index\.php.*/is', WCF::getSession()->requestURI, $requestURI);

			$url .= 'url='.rawurlencode($requestURI[0]);
		}

		// append security token
		$url .= '&t='.SECURITY_TOKEN;

		// append session id
		if (SID != '' && !preg_match('/(?:&|\?)s=[a-z0-9]{40}/', $url)) {
			if (StringUtil::indexOf($url, '?') !== false) {
				$url .= SID_ARG_2ND_NOT_ENCODED;
			}
			else {
				$url .= SID_ARG_1ST;
			}
		}

		return $url;
	}
}
?>