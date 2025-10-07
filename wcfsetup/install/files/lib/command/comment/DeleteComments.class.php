<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\object\type\ObjectType;
use wcf\event\comment\CommentsDeleted;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\response\command\DeleteResponses;
use wcf\system\event\EventHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Deletes a bunch of comments that belong to the same object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class DeleteComments
{
    private readonly ObjectType $objectType;
    private readonly ICommentManager $commentManager;
    /**
     * @var int[]
     */
    private readonly array $commentIDs;

    /**
     * @param Comment[] $comments
     */
    public function __construct(
        private readonly array $comments,
        private readonly bool $updateCounters = true,
    ) {
        $this->commentIDs = \array_column($this->comments, 'commentID');
        $firstCommentID = \array_key_first($this->comments);
        \assert($firstCommentID !== null);
        $firstComment = $this->comments[$firstCommentID];
        $this->objectType = CommentHandler::getInstance()->getObjectType($firstComment->objectTypeID);
        $this->commentManager = CommentHandler::getInstance()->getCommentManagerByID($firstComment->objectTypeID);
    }

    public function __invoke(): void
    {
        $this->deleteActivityEvents();
        $this->deleteNotifications();
        $this->deleteReactions();
        $this->deleteModerationQueues();
        $this->deleteMessageEmbeddedObjects();
        $this->deleteResponses();

        $action = new CommentAction($this->commentIDs, 'delete');
        $action->executeAction();

        $this->updateCounters();

        $event = new CommentsDeleted($this->comments);
        EventHandler::getInstance()->fire($event);
    }

    private function deleteActivityEvents(): void
    {
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.recentActivityEvent')) {
            UserActivityEventHandler::getInstance()->removeEvents(
                $this->objectType->objectType . '.recentActivityEvent',
                $this->commentIDs
            );
        }
    }

    private function deleteNotifications(): void
    {
        if (UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.notification')) {
            UserNotificationHandler::getInstance()->removeNotifications(
                $this->objectType->objectType . '.notification',
                $this->commentIDs
            );
        }
    }

    private function deleteReactions(): void
    {
        ReactionHandler::getInstance()->removeReactions(
            'com.woltlab.wcf.comment',
            $this->commentIDs,
            UserNotificationHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.like.notification')
                ? [$this->objectType->objectType . '.like.notification']
                : []
        );
    }

    private function deleteResponses(): void
    {
        $responseList = new CommentResponseList();
        $responseList->getConditionBuilder()->add('comment_response.commentID IN (?)', [$this->commentIDs]);
        $responseList->readObjects();
        if (!\count($responseList)) {
            return;
        }

        (new DeleteResponses($responseList->getObjects(), $this->updateCounters))();
    }

    private function deleteModerationQueues(): void
    {
        ModerationQueueManager::getInstance()->removeQueues(
            'com.woltlab.wcf.comment.comment',
            $this->commentIDs
        );
    }

    private function deleteMessageEmbeddedObjects(): void
    {
        MessageEmbeddedObjectManager::getInstance()->removeObjects(
            'com.woltlab.wcf.comment',
            $this->commentIDs
        );
    }

    private function updateCounters(): void
    {
        if (!$this->updateCounters) {
            return;
        }

        foreach ($this->comments as $comment) {
            if (!$comment->isDisabled) {
                $this->commentManager->updateCounter($comment->objectID, -1);
            }
        }
    }
}
