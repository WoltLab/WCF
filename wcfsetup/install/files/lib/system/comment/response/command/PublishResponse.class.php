<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\object\type\ObjectType;
use wcf\event\comment\response\ResponsePublished;
use wcf\system\comment\CommentHandler;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\user\notification\object\type\IMultiRecipientCommentUserNotificationObjectType;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Publishes a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PublishResponse
{
    private readonly ObjectType $objectType;
    private readonly Comment $comment;

    public function __construct(
        private readonly CommentResponse $response,
    ) {
        $this->comment = $response->getComment();
        $this->objectType = CommentHandler::getInstance()->getObjectType($this->comment->objectTypeID);
    }

    public function __invoke(): void
    {
        if ($this->response->isDisabled) {
            (new CommentResponseEditor($this->response))->update([
                'isDisabled' => 0,
            ]);
        }

        $commentEditor = new CommentEditor($this->comment);
        $commentEditor->updateCounters(['responses' => 1]);
        // do not prepend the response id as the approved response can appear anywhere
        $commentEditor->updateResponseIDs();

        $this->objectType->getProcessor()->updateCounter($this->comment->objectID, 1);

        $this->fireActivityEvent();
        $this->fireNotificationEvent();

        $event = new ResponsePublished($this->response);
        EventHandler::getInstance()->fire($event);
    }

    private function fireActivityEvent(): void
    {
        if (
            $this->response->userID
            && UserActivityEventHandler::getInstance()->getObjectTypeID(
                $this->objectType->objectType . '.response.recentActivityEvent'
            )
        ) {
            UserActivityEventHandler::getInstance()->fireEvent(
                $this->objectType->objectType . '.response.recentActivityEvent',
                $this->response->responseID,
                null,
                $this->response->userID,
                $this->response->time
            );
        }
    }

    private function fireNotificationEvent(): void
    {
        if (
            !UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.notification')
            || (
                !UserNotificationHandler::getInstance()->getEvent($this->objectType->objectType . '.response.notification', 'commentResponse')
                && !UserNotificationHandler::getInstance()->getEvent($this->objectType->objectType . '.response.notification', 'commentResponseOwner')
            )
        ) {
            return;
        }

        $notificationObject = new CommentResponseUserNotificationObject($this->response);
        $notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($this->objectType->objectType . '.notification');

        if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
            $recipientIDs = $notificationObjectType->getRecipientIDs($this->comment);
        } else {
            $recipientIDs = [];
        }

        $recipientIDs[] = $this->comment->userID;

        $userID = 0;
        if ($notificationObjectType instanceof ICommentUserNotificationObjectType) {
            $userID = $notificationObjectType->getOwnerID($this->comment->commentID);
        }

        // make sure that the response's author gets no notification
        $recipientIDs = \array_diff($recipientIDs, [$this->response->getUserID()]);

        if (UserNotificationHandler::getInstance()->getEvent($this->objectType->objectType . '.response.notification', 'commentResponse')) {
            UserNotificationHandler::getInstance()->fireEvent(
                'commentResponse',
                $this->objectType->objectType . '.response.notification',
                $notificationObject,
                $recipientIDs,
                [
                    'commentID' => $this->comment->commentID,
                    'objectID' => $this->comment->objectID,
                    'userID' => $this->comment->userID,
                ]
            );
        }

        // notify the container owner
        if (UserNotificationHandler::getInstance()->getEvent($this->objectType->objectType . '.response.notification', 'commentResponseOwner')) {
            if ($userID && $userID != $this->comment->userID && $userID != $this->response->getUserID()) {
                UserNotificationHandler::getInstance()->fireEvent(
                    'commentResponseOwner',
                    $this->objectType->objectType . '.response.notification',
                    $notificationObject,
                    [$userID],
                    [
                        'commentID' => $this->comment->commentID,
                        'objectID' => $this->comment->objectID,
                        'objectUserID' => $userID,
                        'userID' => $this->comment->userID,
                    ]
                );
            }
        }
    }
}
