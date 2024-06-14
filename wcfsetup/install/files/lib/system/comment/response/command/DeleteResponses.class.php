<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\object\type\ObjectType;
use wcf\event\comment\response\ResponsesDeleted;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\event\EventHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Deletes a bunch of comment responses that belong to the same object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 *
 * @property-read int[] $responseIDs
 * @property-read CommentResponse[] $responses
 */
final class DeleteResponses
{
    private readonly ObjectType $objectType;
    private readonly ICommentManager $commentManager;

    private readonly array $responseIDs;

    public function __construct(
        private readonly array $responses,
        private readonly bool $updateCounters = true,
    ) {
        $this->responseIDs = \array_column($this->responses, 'responseID');
        foreach ($this->responses as $response) {
            if (!isset($this->objectType)) {
                $this->objectType = CommentHandler::getInstance()->getObjectType($response->getComment()->objectTypeID);
                $this->commentManager = CommentHandler::getInstance()->getCommentManagerByID($response->getComment()->objectTypeID);
            }
        }
    }

    public function __invoke(): void
    {
        $this->deleteActivityEvents();
        $this->deleteNotifications();
        $this->deleteReactions();
        $this->deleteModerationQueues();
        $this->deleteMessageEmbeddedObjects();

        $action = new CommentResponseAction($this->responseIDs, 'delete');
        $action->executeAction();

        $this->updateCounters();

        $event = new ResponsesDeleted($this->responses);
        EventHandler::getInstance()->fire($event);
    }

    private function deleteActivityEvents(): void
    {
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.response.recentActivityEvent')) {
            UserActivityEventHandler::getInstance()->removeEvents(
                $this->objectType->objectType . '.response.recentActivityEvent',
                $this->responseIDs
            );
        }
    }

    private function deleteNotifications(): void
    {
        if (UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.response.notification')) {
            UserNotificationHandler::getInstance()->removeNotifications(
                $this->objectType->objectType . '.response.notification',
                $this->responseIDs
            );
        }
    }

    private function deleteReactions(): void
    {
        ReactionHandler::getInstance()->removeReactions(
            'com.woltlab.wcf.comment.response',
            $this->responseIDs,
            UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.response.like.notification')
                ? [$this->objectType->objectType . '.response.like.notification']
                : []
        );
    }

    private function deleteModerationQueues(): void
    {
        ModerationQueueManager::getInstance()->removeQueues(
            'com.woltlab.wcf.comment.response',
            $this->responseIDs
        );
    }

    private function deleteMessageEmbeddedObjects(): void
    {
        MessageEmbeddedObjectManager::getInstance()->removeObjects(
            'com.woltlab.wcf.comment.response',
            $this->responseIDs
        );
    }

    private function updateCounters(): void
    {
        if (!$this->updateCounters) {
            return;
        }

        foreach ($this->responses as $response) {
            $commentIDs[] = $response->commentID;
        }

        $commentList = new CommentList();
        $commentList->setObjectIDs(\array_unique($commentIDs));
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        foreach ($comments as $comment) {
            $commentEditor = new CommentEditor($comment);
            $commentEditor->updateResponseIDs();
            $commentEditor->updateUnfilteredResponseIDs();
            $commentEditor->updateResponses();
            $commentEditor->updateUnfilteredResponses();
        }

        foreach ($this->responses as $response) {
            if (!$response->isDisabled) {
                $this->commentManager->updateCounter($comments[$response->commentID]->objectID, -1);
            }
        }
    }
}
