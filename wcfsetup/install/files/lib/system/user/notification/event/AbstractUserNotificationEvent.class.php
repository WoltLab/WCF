<?php

namespace wcf\system\user\notification\event;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IFeedEntry;
use wcf\data\language\Language;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Provides a default implementation for user notification events.
 *
 * @author  Joshua Ruesweg, Marcel Werk, Oliver Kliebisch
 * @copyright   2001-2019 WoltLab GmbH, Oliver Kliebisch
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   UserNotificationEvent
 * @extends DatabaseObjectDecorator<UserNotificationEvent>
 */
abstract class AbstractUserNotificationEvent extends DatabaseObjectDecorator implements
    IUserNotificationEvent,
    IFeedEntry
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserNotificationEvent::class;

    /**
     * author object
     * @var UserProfile
     */
    protected $author;

    /**
     * list of authors for stacked notifications
     * @var UserProfile[]
     */
    protected $authors = [];

    /**
     * notification stacking support
     * @var bool
     */
    protected $stackable = false;

    /**
     * user notification
     * @var UserNotification
     */
    protected $notification;

    /**
     * user notification object
     * @var IUserNotificationObject
     */
    protected $userNotificationObject;

    /**
     * additional data for this event
     * @var mixed[]
     */
    protected $additionalData = [];

    /**
     * language object
     * @var Language
     */
    protected $language;

    /**
     * list of point of times for each period's end
     * @var string[]
     */
    protected static $periods = [];

    #[\Override]
    public function setAuthors(array $authors)
    {
        $this->authors = $authors;

        // Ensure that the original author is the first in the list.
        \uasort($this->authors, function ($a, $b) {
            if ($a->userID == $b->userID) {
                return 0;
            }
            if ($a->userID == $this->getAuthorID()) {
                return -1;
            }
            if ($b->userID == $this->getAuthorID()) {
                return 1;
            }

            return 0;
        });
    }

    #[\Override]
    public function setObject(
        UserNotification $notification,
        IUserNotificationObject $object,
        UserProfile $author,
        array $additionalData = []
    ) {
        $this->notification = $notification;
        $this->userNotificationObject = $object;
        $this->author = $author;
        $this->additionalData = $additionalData;
    }

    #[\Override]
    public function getAuthorID()
    {
        return $this->author->userID;
    }

    #[\Override]
    public function getAuthor()
    {
        return $this->author;
    }

    #[\Override]
    public function getAuthors()
    {
        return $this->authors;
    }

    #[\Override]
    public function isVisible()
    {
        return $this->getDecoratedObject()->validateOptions() && $this->getDecoratedObject()->validatePermissions();
    }

    #[\Override]
    public function getEmailTitle()
    {
        return $this->getTitle();
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant')
    {
        return $this->getMessage();
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->userNotificationObject->getObjectID());
    }

    #[\Override]
    public function setLanguage(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Returns the language of this event.
     *
     * @return  Language
     */
    public function getLanguage()
    {
        if ($this->language !== null) {
            return $this->language;
        }

        return WCF::getLanguage();
    }

    #[\Override]
    public function isStackable()
    {
        return $this->stackable;
    }

    /**
     * Returns the readable period matching this notification.
     *
     * @return  string
     */
    public function getPeriod()
    {
        if (empty(self::$periods)) {
            $date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
            $date->setTimezone(WCF::getUser()->getTimeZone());
            $date->setTime(0, 0, 0);

            self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.today');

            // 1 day back
            $date->modify('-1 day');
            self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.yesterday');

            $formatter = \IntlDateFormatter::create(
                WCF::getLanguage()->getLocale(),
                timezone: WCF::getUser()->getTimeZone(),
                pattern: 'EEEE'
            );
            // 2-6 days back
            for ($i = 0; $i < 6; $i++) {
                $date->modify('-1 day');
                self::$periods[$date->getTimestamp()] = $formatter->format($date);
            }
        }

        foreach (self::$periods as $time => $period) {
            if ($this->notification->time >= $time) {
                return $period;
            }
        }

        return WCF::getLanguage()->get('wcf.date.period.older');
    }

    #[\Override]
    public function supportsEmailNotification()
    {
        return true;
    }

    #[\Override]
    public function checkAccess()
    {
        return true;
    }

    #[\Override]
    public function deleteNoAccessNotification()
    {
        return true;
    }

    #[\Override]
    public function isConfirmed()
    {
        return $this->notification->confirmTime > 0;
    }

    #[\Override]
    public function getNotification()
    {
        return $this->notification;
    }

    #[\Override]
    public function getUserNotificationObject()
    {
        return $this->userNotificationObject;
    }

    #[\Override]
    public function getComments()
    {
        return 0;
    }

    #[\Override]
    public function getCategories()
    {
        return [
            $this->notification->objectType,
        ];
    }

    #[\Override]
    public function getExcerpt(int $maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getFormattedMessage(), $maxLength);
    }

    #[\Override]
    public function getFormattedMessage()
    {
        return $this->getMessage();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getFormattedMessage();
    }

    #[\Override]
    public function getTime()
    {
        return $this->getNotification()->time;
    }

    #[\Override]
    public function getUserID()
    {
        return $this->getAuthorID();
    }

    #[\Override]
    public function getUsername()
    {
        return $this->getAuthor()->username;
    }

    #[\Override]
    public function getEventName(): string
    {
        return WCF::getLanguage()->getDynamicVariable("wcf.user.notification.{$this->objectType}.{$this->eventName}");
    }
}
