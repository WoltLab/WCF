<?php
namespace wcf\system\user\notification;
use wcf\data\language\Language;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\system\cache\builder\ICacheBuilder;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\exception\ImplementationException;
use wcf\system\language\LanguageFactory;
use wcf\system\user\notification\event\ITestableUserNotificationEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\MathUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Handles testable user notifications.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification
 * @since	3.1
 */
class TestableUserNotificationEventHandler extends SingletonFactory {
	/**
	 * list of user profiles used as authors
	 * @var	UserProfile[]
	 */
	protected $authors;
	
	/**
	 * notification recipients grouped by language id and based upon the current
	 * user
	 * @var	UserProfile[]
	 */
	protected $recipients = [];
	
	/**
	 * maximum number of authors
	 */
	const MAX_AUTHOR_COUNT = 5;
	
	/**
	 * maximum number of guests
	 */
	const MAX_GUEST_COUNT = 2;
	
	/**
	 * Returns non-persistent user profiles used as authors.
	 * 
	 * @return	UserProfile[]
	 */
	public function getAuthors() {
		if ($this->authors === null) {
			$this->authors = [];
			
			$userProfileList = new UserProfileList();
			$userProfileList->getConditionBuilder()->add('userID <> ?', [$this->getRecipient()->userID]);
			$userProfileList->sqlLimit = self::MAX_AUTHOR_COUNT;
			
			$count = $userProfileList->countObjects();
			
			$languages = LanguageFactory::getInstance()->getLanguages();
			
			while ($count < self::MAX_AUTHOR_COUNT) {
				$username = substr(StringUtil::getRandomID(), 0, 10);
				
				(new UserAction([], 'create', [
					'data' => [
						'email' => $username . '@example.com',
						'languageID' => $languages[array_rand($languages)]->languageID,
						'password' => PasswordUtil::getRandomPassword(),
						'registrationDate' => TIME_NOW - 24 * 3600 * MathUtil::getRandomValue(10, 1000),
						'username' => $username
					]
				]))->executeAction()['returnValues'];
				
				$count++;
			}
			
			$userProfileList->readObjects();
			
			$this->authors = $userProfileList->getObjects();
		}
		
		return $this->authors;
	}
	
	/**
	 * Returns the email body for a user notification email.
	 * 
	 * @param	ITestableUserNotificationEvent	$event
	 * @param	string				$notificationType
	 * @return	string
	 */
	public function getEmailBody(ITestableUserNotificationEvent $event, $notificationType) {
		$email = new Email();
		$email->setSubject($event->getLanguage()->getDynamicVariable('wcf.user.notification.mail.subject', [
			'title' => $event->getEmailTitle()
		]));
		$mailbox = new UserMailbox($this->getRecipient($event->getLanguage())->getDecoratedObject());
		$email->addRecipient($mailbox);
		
		$message = $event->getEmailMessage($notificationType);
		if (is_array($message)) {
			if (!isset($message['variables'])) $message['variables'] = [];
			$variables = array_merge($message['variables'], [
				'notificationContent' => $message,
				'event' => $event,
				'notificationType' => 'instant',
				'variables' => $message['variables'] // deprecated, but is kept for backwards compatibility
			]);
			
			if (isset($message['message-id'])) {
				$email->setMessageID($message['message-id']);
			}
			if (isset($message['in-reply-to'])) {
				foreach ($message['in-reply-to'] as $inReplyTo) $email->addInReplyTo($inReplyTo);
			}
			if (isset($message['references'])) {
				foreach ($message['references'] as $references) $email->addReferences($references);
			}
			
			$email->setBody(new RecipientAwareTextMimePart('text/plain', 'email_notification', 'wcf', $variables));
			
			// generate html version to test for exceptions, but ignore it for rendering
			$html = new RecipientAwareTextMimePart('text/html', 'email_notification', 'wcf', $variables);
			$html->setRecipient($mailbox);
			$html->getContent();
		}
		else {
			$email->setBody(new RecipientAwareTextMimePart('text/plain', 'email_notification', 'wcf', [
				'notificationContent' => $message,
				'event' => $event,
				'notificationType' => 'instant'
			]));
		}
		
		$email->getBody()->setRecipient($mailbox);
		
		return $email->getBodyString();
	}
	
	/**
	 * Returns the recipient of the notifications who is the active user.
	 * 
	 * @param	Language|null	$language
	 * @return	UserProfile
	 */
	public function getRecipient(Language $language = null) {
		if ($language === null) {
			$language = WCF::getUser()->getLanguage();
		}
		
		if (!isset($this->recipients[$language->languageID])) {
			$this->recipients[$language->languageID] = new UserProfile(new User(null, [
				'email' => WCF::getUser()->email,
				'languageID' => $language->languageID,
				'userID' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username
			]));
		}
		
		return $this->recipients[$language->languageID];
	}
	
	/**
	 * Returns a new user notification object based on the given data.
	 *
	 * @param	UserProfile	$author
	 * @param	integer		$timesTriggered
	 * @param	integer		$guestTimesTriggered
	 * @param	array		$additionalData
	 * @return	UserNotification
	 */
	protected function getUserNotification(UserProfile $author, $timesTriggered, $guestTimesTriggered, array $additionalData) {
		return new UserNotification(null, [
			'additionalData' => serialize($additionalData),
			'authorID' => $author->userID,
			'confirmTime' => 0,
			'eventHash' => '',
			'guestTimesTriggered' => $guestTimesTriggered,
			'mailNotified' => 0,
			'time' => TIME_NOW - 60 * 60,
			'timesTriggered' => $timesTriggered,
			'userID' => $this->getRecipient()->userID
		]);
	}
	
	/**
	 * Returns the test user notification events based on the given user notification
	 * event.
	 *
	 * @param	UserNotificationEvent	$userNotificationEvent
	 * @return	ITestableUserNotificationEvent[]
	 */
	public function getUserNotificationEvents(UserNotificationEvent $userNotificationEvent) {
		$className = $userNotificationEvent->className;
		
		if (!is_subclass_of($className, ITestableUserNotificationEvent::class)) {
			throw new ImplementationException($className, ITestableUserNotificationEvent::class);
		}
		
		$authors = $this->getAuthors();
		$firstAuthor = reset($authors);
		
		/** @var ITestableUserNotificationEvent $event */
		$event = new $className($userNotificationEvent);
		
		$minAuthorCount = 0;
		$maxAuthorCount = self::MAX_AUTHOR_COUNT;
		$maxGuestCount = self::MAX_GUEST_COUNT;
		if (!$event->isStackable()) {
			$maxAuthorCount = 1;
			$maxGuestCount = 1;
		}
		
		if (!$className::canBeTriggeredByGuests()) {
			$minAuthorCount = 1;
			$maxGuestCount = 0;
		}
		
		$unknownAuthor = UserProfile::getGuestUserProfile('Unknown Author');
		
		$events = [];
		foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
			for ($authorCount = $minAuthorCount; $authorCount <= $maxAuthorCount; $authorCount++) {
				$localMaxGuestCount = $maxGuestCount;
				if (!$event->isStackable() && $authorCount) {
					$localMaxGuestCount = 0;
				}
				
				for ($guestCount = $authorCount ? 0 : 1; $guestCount <= $localMaxGuestCount; $guestCount++) {
					$objects = $className::getTestObjects($this->getRecipient(), $firstAuthor);
					
					foreach ($objects as $object) {
						$event = new $className($userNotificationEvent);
						$event->setLanguage($language);
						
						$additionalData = $className::getTestAdditionalData($object);
						
						$event->setTestCaseDescription(WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.notificationTest.testCase', [
							'canBeTriggeredByGuests' => $className::canBeTriggeredByGuests(),
							'guestsTriggered' => $guestCount,
							'language' => $language,
							'timesTriggered' => $authorCount
						]));
						
						$event->setObject(
							$this->getUserNotification($firstAuthor, $authorCount + $guestCount, $guestCount, $additionalData),
							$object,
							$authorCount ? $firstAuthor : $unknownAuthor,
							$additionalData
						);
						
						if ($authorCount) {
							$event->setAuthors(array_slice($authors, 0, $authorCount, true));
						}
						else {
							$event->setAuthors([$unknownAuthor]);
						}
						
						$events[] = $event;
					}
				}
			}
		}
		
		return $events;
	}
	
	/**
	 * Forcefully resets the internal data of a cache builder to get up-to-date
	 * data within the same request. This is crucial as during testing, objects
	 * are created and used within the same request.
	 * 
	 * @param	ICacheBuilder	$cacheBuilder
	 */
	public function resetCacheBuilder(ICacheBuilder $cacheBuilder) {
		$reflectionClass = new \ReflectionClass(get_class($cacheBuilder));
		$reflectionProperty = $reflectionClass->getProperty('cache');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($cacheBuilder, []);
	}
}
