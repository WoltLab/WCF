<?php

namespace wcf\system\user\notification\event;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
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
 * @since   3.1
 */
trait TTestableModerationQueueUserNotificationEvent
{
    /**
     * Creates a moderation queue entry for a reported user.
     *
     * @param UserProfile $reportedUser
     * @param UserProfile $reportingUser
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
}
