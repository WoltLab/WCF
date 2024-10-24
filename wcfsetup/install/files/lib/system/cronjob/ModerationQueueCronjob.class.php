<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * Removes moderation queue entries if they're done and older than 30 days.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ModerationQueueCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $sql = "SELECT  queueID
                FROM    wcf1_moderation_queue
                WHERE   status IN (?, ?, ?)
                    AND lastChangeTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            ModerationQueue::STATUS_DONE,
            ModerationQueue::STATUS_REJECTED,
            ModerationQueue::STATUS_CONFIRMED,
            TIME_NOW - (86400 * 30),
        ]);
        $queueIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($queueIDs)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("queueID IN (?)", [$queueIDs]);

            $sql = "DELETE FROM wcf1_moderation_queue
                    " . $conditions;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditions->getParameters());

            // reset moderation count for all users
            ModerationQueueManager::getInstance()->resetModerationCount();

            // Clean up comments associated with these queues.
            CommentHandler::getInstance()->deleteObjects(
                "com.woltlab.wcf.moderation.queue",
                $queueIDs
            );
        }
    }
}
