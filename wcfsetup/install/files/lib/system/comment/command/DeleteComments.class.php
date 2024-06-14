<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\comment\CommentsDeleted;
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
 *
 * @property-read int[] $commentIDs
 * @property-read Comment[] $comments
 */
final class DeleteComments
{
    private readonly ObjectType $objectType;
    private readonly array $commentIDs;

    public function __construct(
        private readonly array $comments,
        private readonly bool $updateCounters = true,
    ) {
        $this->commentIDs = \array_map(fn (Comment $comment): int => $comment->commentID, $this->comments);
        foreach ($this->comments as $comment) {
            if (!isset($this->objectType)) {
                $this->objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
            } else if ($this->objectType->objectTypeID !== $comment->objectTypeID) {
                throw new \InvalidArgumentException('Given comments do not belong to the same object type.');
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
        $responseList->readObjectIDs();
        if (!\count($responseList->getObjectIDs())) {
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
                $this->objectType->getProcessor()->updateCounter($comment->objectID, -1);
            }
        }
    }
}
