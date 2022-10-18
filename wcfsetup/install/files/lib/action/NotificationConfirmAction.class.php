<?php

namespace wcf\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\page\NotificationListPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Marks target notification as confirmed and forwards to the notification URL.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 */
class NotificationConfirmAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * user notification object
     * @var UserNotification
     */
    public $notification;

    /**
     * user notification id
     * @var int
     */
    public $notificationID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->notificationID = (int)$_REQUEST['id'];
        }

        $this->notification = new UserNotification($this->notificationID);
        if (!$this->notification->notificationID) {
            throw new IllegalLinkException();
        }

        if ($this->notification->userID != WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        if (!$this->notification->confirmTime) {
            UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$this->notification->notificationID]);
        }

        $event = new UserNotificationEvent($this->notification->eventID);
        $objectType = ObjectTypeCache::getInstance()->getObjectType($event->objectTypeID);
        $objects = $objectType->getProcessor()->getObjectsByIDs([$this->notification->objectID]);

        $userProfile = null;
        if ($this->notification->authorID) {
            $userProfile = new UserProfile(new User($this->notification->authorID));
        } else {
            $userProfile = new UserProfile(new User(
                null,
                ['userID' => null, 'username' => WCF::getLanguage()->get('wcf.user.guest')]
            ));
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

        // The notification link can be `null` (e.g. for some moderation notifications).
        // This would trigger an exception further in the code, because the PSR7 redirect response
        // expect a real URL. For this reason, we rewrite `null` with a link to the NotificationListPage.
        $link = $notificationEvent->getLink();
        if ($link === null) {
            $link = LinkHandler::getInstance()->getControllerLink(NotificationListPage::class);
        }

        return new RedirectResponse($link);
    }
}
