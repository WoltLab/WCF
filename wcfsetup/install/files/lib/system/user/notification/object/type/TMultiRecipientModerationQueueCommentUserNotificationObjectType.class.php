<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\comment\Comment;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Implements IMultiRecipientCommentUserNotificationObjectType::getRecipientIDs()
 * for moderation queue comment user notification object types.
 *
 * @author  Mathias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
trait TMultiRecipientModerationQueueCommentUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    public function getRecipientIDs(Comment $comment)
    {
        $objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue');
        if ($comment->objectTypeID != $objectTypeID) {
            return [];
        }

        $this->loadModerators($comment);

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('queueID = ?', [$comment->objectID]);
        $conditionBuilder->add('isAffected = ?', [1]);
        $sql = "SELECT  userID
                FROM    wcf1_moderation_queue_to_user
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $recipientIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        // make sure that all users (still) have permission to access moderation
        if (!$recipientIDs) {
            UserStorageHandler::getInstance()->loadStorage($recipientIDs);
            $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($recipientIDs);
            $recipientIDs = \array_keys(\array_filter($userProfiles, static function (UserProfile $userProfile) {
                return $userProfile->getPermission('mod.general.canUseModeration');
            }));
        }

        return $recipientIDs;
    }

    private function loadModerators(Comment $comment): void
    {
        $queue = new ModerationQueue($comment->objectID);
        $objectType = ObjectTypeCache::getInstance()->getObjectType($queue->objectTypeID);
        $canUseModerationOption = UserGroupOptionCacheBuilder::getInstance()->getData()['options']['mod.general.canUseModeration'];
        $processor = $objectType->getProcessor();

        \assert($processor instanceof IModerationQueueHandler);

        // Load all userIDs, which have the permission to access the moderation AND which
        // have no entry in the table wcf1_moderation_queue_to_user for the given queue.
        // The wcf1_moderation_queue_to_user table caches the access to the queue item with
        // the isAffected column, so we don't need to calculate the access for these users.
        // For performance reasons, the query is also limited to 100 userIDs, because each
        // permission calculation could perform own SQL queries within the calculation and we
        // have to calculate the permissions for each user separately.
        $sql = "SELECT  DISTINCT userID
                FROM    (
                            SELECT  userID
                            FROM    wcf1_user_to_group
                            WHERE   groupID IN (
                                SELECT  groupID
                                FROM    wcf1_user_group_option_value
                                WHERE   optionID = ?
                                    AND optionValue = ?
                            )
                        ) users_in_groups_with_access
                WHERE   userID NOT IN (
                            SELECT  userID
                            FROM    wcf1_user_to_group
                            WHERE   groupID IN (
                                        SELECT  groupID
                                        FROM    wcf1_user_group_option_value
                                        WHERE   optionID = ?
                                            AND optionValue = ?
                                    )
                        )
                    AND userID NOT IN (
                            SELECT  userID
                            FROM    wcf1_moderation_queue_to_user
                            WHERE   queueID = ?
                        )";
        $statement = WCF::getDB()->prepare($sql, 100);
        $statement->execute([
            $canUseModerationOption->optionID,
            1,
            $canUseModerationOption->optionID,
            -1,
            $queue->queueID,
        ]);

        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($userIDs) {
            UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);

            foreach ($userIDs as $userID) {
                ModerationQueueManager::getInstance()->setAssignment([
                    $queue->queueID => $processor->isAffectedUser($queue, $userID),
                ], UserProfileRuntimeCache::getInstance()->getObject($userID)->getDecoratedObject());
            }
        }
    }
}
