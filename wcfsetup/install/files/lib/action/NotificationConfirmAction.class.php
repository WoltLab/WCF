<?php

namespace wcf\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Marks target notification as confirmed and forwards to the notification URL.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class NotificationConfirmAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * user notification object
     */
    public ?UserNotification $notification = null;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->notification = Helper::fetchObjectFromQueryParameter(UserNotification::class);

        if ($this->notification->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    public function execute(): RedirectResponse
    {
        parent::execute();

        if ($this->notification->confirmTime === 0) {
            UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$this->notification->notificationID]);
        }

        $event = new UserNotificationEvent($this->notification->eventID);
        $objectType = ObjectTypeCache::getInstance()->getObjectType($event->objectTypeID);
        $objects = $objectType->getProcessor()->getObjectsByIDs([$this->notification->objectID]);

        $userProfile = null;
        if ($this->notification->authorID !== null) {
            $userProfile = new UserProfile(new User($this->notification->authorID));
        } else {
            $userProfile = UserProfile::getGuestUserProfile(WCF::getLanguage()->get('wcf.user.guest'));
        }

        $className = $event->className;

        /** @var IUserNotificationEvent $notificationEvent */
        $notificationEvent = new $className($event);
        $notificationEvent->setObject(
            $this->notification,
            $objects[$this->notification->objectID],
            $userProfile,
            $this->notification->additionalData
        );

        return new RedirectResponse($notificationEvent->getLink());
    }
}
