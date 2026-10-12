<?php

namespace wcf\system\user\notification\event;

use wcf\acp\page\UserListPage;
use wcf\data\user\UserProfile;
use wcf\page\UserPage;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\UserRegistrationUserNotificationObject;

/**
 * Notification event for users that need activation by an admin.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @method  UserRegistrationUserNotificationObject  getUserNotificationObject()
 */
class UserNeedActivationUserNotificationEvent extends AbstractUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected $stackable = true;

    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        return [new UserRegistrationUserNotificationObject($author->getDecoratedObject())];
    }

    #[\Override]
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.userNeedActivation.title.stacked',
                ['count' => $count]
            );
        }

        return $this->getLanguage()->get('wcf.user.notification.userNeedActivation.title');
    }

    #[\Override]
    public function getMessage()
    {
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.userNeedActivation.message.stacked',
                [
                    'author' => $this->author,
                    'authors' => $authors,
                    'count' => $count,
                    'others' => $count - 1,
                ]
            );
        }

        return $this->getLanguage()->getDynamicVariable(
            'wcf.user.notification.userNeedActivation.message',
            [
                'author' => $this->author,
                'notification' => $this->notification,
                'username' => $this->getUserNotificationObject()->username,
                'userNotificationObject' => $this->getUserNotificationObject(),
            ]
        );
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): array
    {
        return [
            'template' => 'email_notification_userNeedActivation',
            'application' => 'wcf',
            'variables' => [
                'notification' => $this->notification,
                'username' => $this->getUserNotificationObject()->username,
                'userNotificationObject' => $this->getUserNotificationObject(),
            ],
        ];
    }

    #[\Override]
    public function getLink(): string
    {
        if (\count($this->getAuthors()) > 1) {
            return LinkHandler::getInstance()->getControllerLink(UserListPage::class, [
                'filters' => ['pendingActivation' => 1],
            ]);
        }

        return LinkHandler::getInstance()->getControllerLink(UserPage::class, [
            'object' => $this->getUserNotificationObject(),
        ]);
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1((string)$this->eventID);
    }
}
