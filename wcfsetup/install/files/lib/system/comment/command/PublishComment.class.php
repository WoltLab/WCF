<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\object\type\ObjectType;
use wcf\event\comment\CommentPublished;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\user\notification\object\type\IMultiRecipientCommentUserNotificationObjectType;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Publishes a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PublishComment
{
    private readonly ObjectType $objectType;
    private readonly ICommentManager $commentManager;

    public function __construct(
        private readonly Comment $comment,
    ) {
        $this->objectType = CommentHandler::getInstance()->getObjectType($this->comment->objectTypeID);
        $this->commentManager = CommentHandler::getInstance()->getCommentManagerByID($this->comment->objectTypeID);
    }

    public function __invoke(): void
    {
        if ($this->comment->isDisabled) {
            (new CommentEditor($this->comment))->update([
                'isDisabled' => 0
            ]);
        }
        $this->commentManager->updateCounter($this->comment->objectID, 1);

        $this->fireActivityEvent();
        $this->fireNotificationEvent();

        $event = new CommentPublished($this->comment);
        EventHandler::getInstance()->fire($event);
    }

    private function fireActivityEvent(): void
    {
        if (
            $this->comment->userID
            && UserActivityEventHandler::getInstance()->getObjectTypeID(
                $this->objectType->objectType . '.recentActivityEvent'
            )
        ) {
            UserActivityEventHandler::getInstance()->fireEvent(
                $this->objectType->objectType . '.recentActivityEvent',
                $this->comment->commentID,
                null,
                $this->comment->userID,
                $this->comment->time
            );
        }
    }

    private function fireNotificationEvent(): void
    {
        if (!UserNotificationHandler::getInstance()->getEvent($this->objectType->objectType . '.notification', 'comment')) {
            return;
        }

        $notificationObject = new CommentUserNotificationObject($this->comment);
        $notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor(
            $this->objectType->objectType . '.notification'
        );

        if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
            $recipientIDs = $notificationObjectType->getRecipientIDs($this->comment);
        } else {
            $recipientIDs = [];
        }

        if ($notificationObjectType instanceof ICommentUserNotificationObjectType) {
            $recipientIDs[] = $notificationObjectType->getOwnerID($this->comment->commentID);
        }

        // make sure that the comment's author gets no notification
        $recipientIDs = \array_diff($recipientIDs, [$this->comment->getUserID()]);

        UserNotificationHandler::getInstance()->fireEvent(
            'comment',
            $this->objectType->objectType . '.notification',
            $notificationObject,
            $recipientIDs
        );
    }
}
