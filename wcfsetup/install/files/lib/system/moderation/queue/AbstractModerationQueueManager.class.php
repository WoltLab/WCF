<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ModerationQueueList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for moderation queue managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractModerationQueueManager extends SingletonFactory implements IModerationQueueManager
{
    /**
     * definition name
     * @var string
     */
    protected $definitionName = '';

    /**
     * @inheritDoc
     */
    public function assignQueues($objectTypeID, array $queues)
    {
        ModerationQueueManager::getInstance()
            ->getProcessor($this->definitionName, null, $objectTypeID)
            ->assignQueues($queues);
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectType, $objectID = null)
    {
        return ModerationQueueManager::getInstance()->isValid($this->definitionName, $objectType);
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeID($objectType)
    {
        return ModerationQueueManager::getInstance()->getObjectTypeID($this->definitionName, $objectType);
    }

    /**
     * @inheritDoc
     */
    public function getProcessor($objectType, $objectTypeID = null)
    {
        return ModerationQueueManager::getInstance()->getProcessor($this->definitionName, $objectType, $objectTypeID);
    }

    /**
     * @inheritDoc
     */
    public function populate($objectTypeID, array $objects)
    {
        ModerationQueueManager::getInstance()
            ->getProcessor($this->definitionName, null, $objectTypeID)
            ->populate($objects);
    }

    /**
     * @inheritDoc
     */
    public function canRemoveContent(ModerationQueue $queue)
    {
        return $this->getProcessor(null, $queue->objectTypeID)->canRemoveContent($queue);
    }

    /**
     * @inheritDoc
     */
    public function removeContent(ModerationQueue $queue, $message = '')
    {
        $this->getProcessor(null, $queue->objectTypeID)->removeContent($queue, $message);
    }

    /**
     * Adds an entry to moderation queue.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @param int $containerID
     * @param array $additionalData
     */
    protected function addEntry($objectTypeID, $objectID, $containerID = 0, array $additionalData = [])
    {
        $sql = "SELECT  queueID
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
        ]);
        $row = $statement->fetchArray();

        if ($row === false) {
            $objectAction = new ModerationQueueAction([], 'create', [
                'data' => [
                    'objectTypeID' => $objectTypeID,
                    'objectID' => $objectID,
                    'containerID' => $containerID,
                    'userID' => WCF::getUser()->userID ?: null,
                    'time' => TIME_NOW,
                    'additionalData' => \serialize($additionalData),
                ],
            ]);
            $objectAction->executeAction();
        } else {
            $objectAction = new ModerationQueueAction([$row['queueID']], 'update', [
                'data' => [
                    'status' => ModerationQueue::STATUS_OUTSTANDING,
                    'containerID' => $containerID,
                    'userID' => WCF::getUser()->userID ?: null,
                    'time' => TIME_NOW,
                    'additionalData' => \serialize($additionalData),
                ],
            ]);
            $objectAction->executeAction();
        }

        ModerationQueueManager::getInstance()->resetModerationCount();
    }

    /**
     * Adds multiple entries to moderation queue at once.
     *
     * In contrast to `addModeratedContent()`, this method expects the container ids to be
     * passed as a parameter. If no container id is given for a specific object id, `0` is
     * used as container id.
     *
     * This method is intended for bulk processing.
     *
     * @param int $objectTypeID
     * @param int[] $objectIDs
     * @param int[] $containerIDs format: `objectID => containerID`
     * @param array $additionalData
     */
    protected function addEntries($objectTypeID, array $objectIDs, array $containerIDs, array $additionalData = [])
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
        $conditionBuilder->add('objectID IN (?)', [$objectIDs]);

        $sql = "SELECT  queueID, objectID
                FROM    wcf1_moderation_queue
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $existingQueueIDs = $statement->fetchMap('objectID', 'queueID');

        // create new moderation queue entries for new objects
        $newObjectIDs = \array_diff($objectIDs, \array_keys($existingQueueIDs));

        $itemsPerLoop = 1000;
        $batchCount = \ceil(\count($newObjectIDs) / $itemsPerLoop);
        $userID = WCF::getUser()->userID ?: null;
        $serializedData = \serialize($additionalData);

        WCF::getDB()->beginTransaction();
        for ($i = 0; $i < $batchCount; $i++) {
            $batchObjectIDs = \array_slice($newObjectIDs, $i * $itemsPerLoop, $itemsPerLoop);

            $parameters = [];
            foreach ($batchObjectIDs as $objectID) {
                $parameters = \array_merge($parameters, [
                    $objectTypeID,
                    $objectID,
                    $containerIDs[$objectID] ?? 0,
                    $userID,
                    TIME_NOW,
                    TIME_NOW,
                    $serializedData,
                ]);
            }

            $sql = "INSERT INTO wcf1_moderation_queue
                                (objectTypeID, objectID, containerID, userID, time, lastChangeTime, additionalData)
                    VALUES      (?, ?, ?, ?, ?, ?, ?)" . \str_repeat(
                ', (?, ?, ?, ?, ?, ?, ?)',
                \count($batchObjectIDs) - 1
            );
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($parameters);
        }
        WCF::getDB()->commitTransaction();

        // update existing moderation queue entries

        // group queue ids by container id
        $groupedQueueIDs = [];
        foreach ($existingQueueIDs as $objectID => $queueID) {
            $containerID = $containerIDs[$objectID] ?? 0;

            if (!isset($groupedQueueIDs[$containerID])) {
                $groupedQueueIDs[$containerID] = [];
            }
            $groupedQueueIDs[$containerID][] = $queueID;
        }

        WCF::getDB()->beginTransaction();
        foreach ($groupedQueueIDs as $containerID => $queueIDs) {
            $batchCount = \ceil(\count($queueIDs) / $itemsPerLoop);

            for ($i = 0; $i < $batchCount; $i++) {
                $batchQueueIDs = \array_slice($queueIDs, $i * $itemsPerLoop, $itemsPerLoop);

                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('queueID IN (?)', [$batchQueueIDs]);

                $sql = "UPDATE  wcf1_moderation_queue
                        SET     status = ?,
                                containerID = ?,
                                userID = ?,
                                time = ?,
                                lastChangeTime = ?,
                                additionalData = ?
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute(\array_merge(
                    [
                        ModerationQueue::STATUS_OUTSTANDING,
                        $containerID,
                        $userID,
                        TIME_NOW,
                        TIME_NOW,
                        $serializedData,
                    ],
                    $conditionBuilder->getParameters()
                ));
            }
        }
        WCF::getDB()->commitTransaction();

        ModerationQueueManager::getInstance()->resetModerationCount();
    }

    /**
     * Marks a list of moderation queue entries as done.
     *
     * @param int $objectTypeID
     * @param int[] $objectIDs
     */
    protected function removeEntries($objectTypeID, array $objectIDs)
    {
        $queueList = new ModerationQueueList();
        $queueList->getConditionBuilder()->add("moderation_queue.objectTypeID = ?", [$objectTypeID]);
        $queueList->getConditionBuilder()->add("moderation_queue.objectID IN (?)", [$objectIDs]);
        $queueList->readObjects();

        if (\count($queueList)) {
            $objectAction = new ModerationQueueAction($queueList->getObjects(), 'markAsDone');
            $objectAction->executeAction();
        }
    }
}
