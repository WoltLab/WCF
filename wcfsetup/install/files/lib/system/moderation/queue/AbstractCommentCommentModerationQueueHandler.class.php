<?php

namespace wcf\system\moderation\queue;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\ViewableComment;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\manager\ICommentPermissionManager;
use wcf\system\WCF;

/**
 * An abstract implementation of IModerationQueueHandler for comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractCommentCommentModerationQueueHandler extends AbstractModerationQueueHandler
{
    /**
     * @inheritDoc
     */
    protected $className = Comment::class;

    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.comment.comment';

    /**
     * list of comment managers
     * @var ICommentManager[]
     */
    protected static $commentManagers = [];

    /**
     * @inheritDoc
     */
    public function assignQueues(array $queues)
    {
        $assignments = [];

        // read comments
        $commentIDs = [];
        foreach ($queues as $queue) {
            $commentIDs[] = $queue->objectID;
        }

        $comments = CommentRuntimeCache::getInstance()->getObjects($commentIDs);

        $orphanedQueueIDs = [];
        foreach ($queues as $queue) {
            $assignUser = false;

            if ($comments[$queue->objectID] === null) {
                $orphanedQueueIDs[] = $queue->queueID;
                continue;
            }

            $comment = $comments[$queue->objectID];
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
    public function getContainerID($objectID)
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        if ($this->getComment($objectID) === null) {
            return false;
        }

        return true;
    }

    /**
     * Returns a comment object by comment id or null if comment id is invalid.
     *
     * @param int $objectID
     * @return  Comment|null
     */
    protected function getComment($objectID)
    {
        return CommentRuntimeCache::getInstance()->getObject($objectID);
    }

    /**
     * Returns a comment manager for given comment.
     *
     * @param Comment $comment
     * @return  ICommentManager
     */
    protected function getCommentManager(Comment $comment)
    {
        if (!isset(self::$commentManagers[$comment->objectTypeID])) {
            self::$commentManagers[$comment->objectTypeID] = ObjectTypeCache::getInstance()
                ->getObjectType($comment->objectTypeID)
                ->getProcessor();
        }

        return self::$commentManagers[$comment->objectTypeID];
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

        // fetch comments
        $comments = CommentRuntimeCache::getInstance()->getObjects($objectIDs);
        foreach ($queues as $object) {
            if ($comments[$object->objectID] !== null) {
                $object->setAffectedObject($comments[$object->objectID]);
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
            $commentAction = new CommentAction([$this->getComment($queue->objectID)], 'delete');
            $commentAction->executeAction();
        }
    }

    /**
     * Returns the parsed template for the target comment.
     *
     * @param ViewableModerationQueue $queue
     * @return      string
     */
    protected function getRelatedContent(ViewableModerationQueue $queue)
    {
        WCF::getTPL()->assign([
            'message' => ViewableComment::getComment($queue->objectID),
        ]);

        return WCF::getTPL()->fetch('moderationComment');
    }

    #[\Override]
    public function isAffectedUser(ModerationQueue $queue, $userID)
    {
        if (!parent::isAffectedUser($queue, $userID)) {
            return false;
        }
        $comment = $this->getComment($queue->objectID);
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
