<?php

namespace wcf\system\user\notification\event;

use wcf\data\comment\CommentBuilder;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\comment\CommentHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;

/**
 * Provides a method to create a moderation queue entry for testing user notification
 * events.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TTestableModerationQueueUserNotificationEvent
{
    /**
     * Creates a moderation queue entry for a reported user.
     *
     * @return  ModerationQueue
     */
    public static function getTestUserModerationQueueEntry(UserProfile $reportedUser, UserProfile $reportingUser)
    {
        $objectTypeID = ModerationQueueReportManager::getInstance()->getObjectTypeID('com.woltlab.wcf.user');

        $originalUser = WCF::getUser();
        WCF::getSession()->changeUser($reportingUser->getDecoratedObject(), true);

        ModerationQueueReportManager::getInstance()->addReport(
            ObjectTypeCache::getInstance()->getObjectType($objectTypeID)->objectType,
            $reportedUser->userID,
            'Report Message'
        );

        WCF::getSession()->changeUser($originalUser, true);

        $sql = "SELECT  *
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectTypeID, $reportedUser->userID]);

        $moderationQueue = $statement->fetchObject(ModerationQueue::class);

        ModerationQueueManager::getInstance()->setAssignment([$moderationQueue->queueID => true]);

        return $moderationQueue;
    }

    /**
     * Creates a moderation queue entry for a disabled comment that awaits approval.
     *
     * @since 6.3
     */
    public static function getTestActivationModerationQueueEntry(
        UserProfile $recipient,
        UserProfile $author
    ): ModerationQueue {
        $commentObjectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
        $comment = CommentBuilder::forCreate()
            ->setObjectType(ObjectTypeCache::getInstance()->getObjectType($commentObjectTypeID))
            ->setObjectID($recipient->userID)
            ->setTime(\TIME_NOW - 10)
            ->setUser($author->getDecoratedObject())
            ->setMessage('<p>Test Comment</p>')
            ->setEnableHtml(true)
            ->setIsDisabled(true)
            ->create();

        // The queue entry is created directly to avoid notifying the actual moderators.
        $moderationQueue = new ModerationQueueAction([], 'create', [
            'data' => [
                'objectTypeID' => ModerationQueueActivationManager::getInstance()->getObjectTypeID(
                    'com.woltlab.wcf.comment.comment'
                ),
                'objectID' => $comment->commentID,
                'userID' => $author->userID,
                'time' => \TIME_NOW,
                'additionalData' => \serialize([]),
            ],
        ])->executeAction()['returnValues'];
        \assert($moderationQueue instanceof ModerationQueue);

        ModerationQueueManager::getInstance()->setAssignment([$moderationQueue->queueID => true]);

        return $moderationQueue;
    }
}
