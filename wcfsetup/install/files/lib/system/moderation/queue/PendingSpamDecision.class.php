<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Tells whether a user is waiting for the moderators to decide on content that was
 * flagged as suspected spam. That is the case while an open activation entry for content
 * of the user, or an open report against the user, carries the spam marker.
 *
 * The state is derived from the moderation queue on every call, so it ends as soon as
 * the entry is resolved.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PendingSpamDecision
{
    private function __construct() {}

    public static function existsFor(User $user): bool
    {
        if ($user->userID === 0) {
            return false;
        }

        return self::hasSuspectedSpamContent($user) || self::isReportedAsSuspectedSpammer($user);
    }

    /**
     * The `userID` of an activation entry is the user whose request filed it, which is
     * the author of the disabled content.
     */
    private static function hasSuspectedSpamContent(User $user): bool
    {
        $objectTypeIDs = \array_values(\array_map(
            static fn(ObjectType $objectType) => $objectType->objectTypeID,
            ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.moderation.activation')
        ));
        if ($objectTypeIDs === []) {
            return false;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('userID = ?', [$user->userID]);
        $conditions->add('objectTypeID IN (?)', [$objectTypeIDs]);
        $conditions->add('status IN (?)', [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]);

        $sql = "SELECT  queueID, additionalData
                FROM    wcf1_moderation_queue
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        return self::containsSuspectedSpam($statement->fetchObjects(ModerationQueue::class));
    }

    private static function isReportedAsSuspectedSpammer(User $user): bool
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
            'com.woltlab.wcf.moderation.report',
            'com.woltlab.wcf.user'
        );
        if ($objectTypeID === null) {
            return false;
        }

        $sql = "SELECT  queueID, additionalData
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status IN (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $user->userID,
            ModerationQueue::STATUS_OUTSTANDING,
            ModerationQueue::STATUS_PROCESSING,
        ]);

        return self::containsSuspectedSpam($statement->fetchObjects(ModerationQueue::class));
    }

    /**
     * @param ModerationQueue[] $queues
     */
    private static function containsSuspectedSpam(array $queues): bool
    {
        foreach ($queues as $queue) {
            if ($queue->isSuspectedSpam()) {
                return true;
            }
        }

        return false;
    }
}
