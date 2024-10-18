<?php

namespace wcf\system\moderation\queue;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\ViewableCommentResponse;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\cache\runtime\CommentResponseRuntimeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\ICommentPermissionManager;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueHandler for comment responses.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractCommentResponseModerationQueueHandler extends AbstractCommentCommentModerationQueueHandler
{
    /**
     * @inheritDoc
     */
    protected $className = CommentResponse::class;

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.comment.response';

    /**
     * @inheritDoc
     */
    public function assignQueues(array $queues)
    {
        $assignments = [];

        // read comments and responses
        $responseIDs = [];
        foreach ($queues as $queue) {
            $responseIDs[] = $queue->objectID;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("comment_response.responseID IN (?)", [$responseIDs]);

        $sql = "SELECT      comment_response.responseID, comment.commentID, comment.objectTypeID, comment.objectID
                FROM        wcf1_comment_response comment_response
                LEFT JOIN   wcf1_comment comment
                ON          comment.commentID = comment_response.commentID
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $comments = $responses = [];
        while ($row = $statement->fetchArray()) {
            $comments[$row['commentID']] = new Comment(null, $row);
            $responses[$row['responseID']] = new CommentResponse(null, $row);
        }

        $orphanedQueueIDs = [];
        foreach ($queues as $queue) {
            $assignUser = false;

            if (!isset($responses[$queue->objectID]) || !isset($comments[$responses[$queue->objectID]->commentID])) {
                $orphanedQueueIDs[] = $queue->queueID;
                continue;
            }

            $comment = $comments[$responses[$queue->objectID]->commentID];
            if ($this->getCommentManager($comment)->canModerate($comment->objectTypeID, $comment->objectID)) {
                $assignUser = true;
            }

            $assignments[$queue->queueID] = $assignUser;
        }

        ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
        ModerationQueueManager::getInstance()->setAssignment($assignments);
    }

    /**
     * @inheritDoc
     */
    public function getRelatedContent(ViewableModerationQueue $queue)
    {
        WCF::getTPL()->assign([
            'message' => ViewableCommentResponse::getResponse($queue->objectID),
        ]);

        return WCF::getTPL()->fetch('moderationComment');
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        if ($this->getResponse($objectID) === null) {
            return false;
        }

        return true;
    }

    /**
     * Returns a comment response object by response id or null if response id is invalid.
     *
     * @param int $objectID
     * @return  CommentResponse|null
     */
    protected function getResponse($objectID)
    {
        return CommentResponseRuntimeCache::getInstance()->getObject($objectID);
    }

    /**
     * @inheritDoc
     */
    public function populate(array $queues)
    {
        $objectIDs = [];
        foreach ($queues as $object) {
            $objectIDs[] = $object->objectID;
        }

        $responses = CommentResponseRuntimeCache::getInstance()->getObjects($objectIDs);

        $commentIDs = [];
        foreach ($responses as $response) {
            if ($response !== null) {
                $commentIDs[] = $response->commentID;
            }
        }

        $comments = [];
        if (!empty($commentIDs)) {
            $comments = CommentRuntimeCache::getInstance()->getObjects($commentIDs);
        }

        foreach ($queues as $object) {
            if ($responses[$object->objectID] !== null) {
                $response = $responses[$object->objectID];
                $response->setComment($comments[$response->commentID]);

                $object->setAffectedObject($response);
            } else {
                $object->setIsOrphaned();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function removeContent(ModerationQueue $queue, $message)
    {
        if ($this->isValid($queue->objectID)) {
            $responseAction = new CommentResponseAction([$this->getResponse($queue->objectID)], 'delete');
            $responseAction->executeAction();
        }
    }

    #[\Override]
    public function isAffectedUser(ModerationQueue $queue, $userID)
    {
        if (!AbstractModerationQueueHandler::isAffectedUser($queue, $userID)) {
            return false;
        }
        $response = $this->getResponse($queue->objectID);
        if ($response === null) {
            return false;
        }
        $comment = $this->getComment($response->commentID);
        if ($comment === null) {
            return false;
        }
        $manager = $this->getCommentManager($comment);
        if (!($manager instanceof ICommentPermissionManager)) {
            return false;
        }

        return $manager->canModerateObject(
            $comment->objectTypeID,
            $comment->objectID,
            UserProfileRuntimeCache::getInstance()->getObject($userID)
        );
    }
}
