<?php

namespace wcf\system\user\notification\event;

use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowAction;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;

/**
 * Notification event for followers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserFollowUserNotificationObject    getUserNotificationObject()
 */
class UserFollowFollowingUserNotificationEvent extends AbstractUserNotificationEvent implements
    ITestableUserNotificationEvent
{
    use TTestableUserNotificationEvent;

    /**
     * @inheritDoc
     */
    protected $stackable = true;

    #[\Override]
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable(
                'wcf.user.notification.follow.title.stacked',
                ['count' => $count]
            );
        }

        return $this->getLanguage()->get('wcf.user.notification.follow.title');
    }

    #[\Override]
    public function getMessage()
    {
        $authors = \array_values($this->getAuthors());
        $count = \count($authors);

        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('wcf.user.notification.follow.message.stacked', [
                'author' => $this->author,
                'authors' => $authors,
                'count' => $count,
                'others' => $count - 1,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable(
            'wcf.user.notification.follow.message',
            ['author' => $this->author]
        );
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): array
    {
        return [
            'template' => 'email_notification_userFollowFollowing',
            'application' => 'wcf',
        ];
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->author->getLink();
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->followUserID);
    }

    /**
     * @return  UserFollowUserNotificationObject[]
     */
    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        $follow = UserFollow::getFollow($recipient->userID, $author->userID);
        if ($follow->isNil()) {
            $follow = (new UserFollowAction([], 'create', [
                'data' => [
                    'userID' => $recipient->userID,
                    'followUserID' => $author->userID,
                    'time' => \TIME_NOW - 60 * 60,
                ],
            ]))->executeAction()['returnValues'];
        }

        return [new UserFollowUserNotificationObject($follow)];
    }

    #[\Override]
    public static function getTestAdditionalData(IUserNotificationObject $object)
    {
        /** @var UserFollowUserNotificationObject $object */

        return [$object->followUserID];
    }
}
