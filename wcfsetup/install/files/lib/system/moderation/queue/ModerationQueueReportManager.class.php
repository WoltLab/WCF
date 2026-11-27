<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\user\User;
use wcf\form\ModerationReportForm;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\WCF;

/**
 * Moderation queue implementation for reports.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @method IModerationQueueReportHandler getProcessor(?string $objectType, ?int $objectTypeID = null)
 */
class ModerationQueueReportManager extends AbstractModerationQueueManager
{
    /**
     * @inheritDoc
     */
    protected $definitionName = 'com.woltlab.wcf.moderation.report';

    /**
     * Capacity of the `TEXT` column `additionalData` in bytes.
     */
    private const ADDITIONAL_DATA_MAX_BYTES = 65_535;

    /**
     * Returns true if given item was already reported.
     *
     * @return  bool
     */
    public function isAlreadyReported(string $objectType, int $objectID)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
        ]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Returns true if the object with the given data has a pending report.
     * A pending report has a status other than done.
     *
     * @return  bool
     */
    public function hasPendingReport(string $objectType, int $objectID)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status IN (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
            ModerationQueue::STATUS_OUTSTANDING,
            ModerationQueue::STATUS_PROCESSING,
        ]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Returns true if current user can report given content.
     *
     * @return  bool
     */
    public function canReport(string $objectType, int $objectID)
    {
        return $this->getProcessor($objectType)->canReport($objectID);
    }

    #[\Override]
    public function getController(): string
    {
        return ModerationReportForm::class;
    }

    /**
     * Returns rendered template for reported content.
     *
     * @param ViewableModerationQueue $queue
     * @return  string
     */
    public function getReportedContent(ViewableModerationQueue $queue)
    {
        return $this->getProcessor(null, $queue->objectTypeID)->getReportedContent($queue);
    }

    /**
     * Returns the reported object.
     *
     * @return  \wcf\data\IUserContent
     */
    public function getReportedObject(string $objectType, int $objectID)
    {
        return $this->getProcessor($objectType)->getReportedObject($objectID);
    }

    /**
     * Adds a report for specified content.
     *
     * @param mixed[] $additionalData
     * @return void
     * @throws  InvalidObjectTypeException
     */
    public function addReport(string $objectType, int $objectID, string $message, array $additionalData = [])
    {
        if (!$this->isValid($objectType)) {
            throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.moderation.report');
        }

        $additionalData['message'] = $message;
        $this->addEntry(
            $this->getObjectTypeID($objectType),
            $objectID,
            $this->getProcessor($objectType)->getContainerID($objectID),
            $additionalData
        );
    }

    /**
     * Files a report against a user because a message of the user was rejected as suspected
     * spam. The report has no reporting user, it is shown as filed by the system. The user
     * cannot create new messages until the report has been closed, see `PendingSpamDecision`.
     *
     * An open report against the user is not replaced, it keeps its reporter, message and
     * time and receives the detections, replacing those of an earlier spam report.
     *
     * @param array<string, array<array-key, mixed>> $detections see `MessageSpamChecking::getDetections()`
     * @since 6.3
     */
    public function addSuspectedSpamReport(User $user, array $detections): void
    {
        if (!$this->isValid('com.woltlab.wcf.user')) {
            throw new InvalidObjectTypeException('com.woltlab.wcf.user', 'com.woltlab.wcf.moderation.report');
        }
        $objectTypeID = $this->getObjectTypeID('com.woltlab.wcf.user');

        $existingQueue = $this->getReusableEntry($objectTypeID, $user->userID);
        if ($existingQueue !== null && !$existingQueue->isDone()) {
            $this->markOpenReportAsSuspectedSpam($existingQueue, $detections);

            return;
        }

        $additionalData = [
            'message' => '',
            ModerationQueue::SPAM_DETECTIONS_KEY => $detections,
        ];

        $data = [
            'containerID' => $this->getProcessor('com.woltlab.wcf.user')->getContainerID($user->userID),
            'userID' => null,
            'time' => \TIME_NOW,
            'additionalData' => \serialize($additionalData),
        ];

        if ($existingQueue === null) {
            $objectAction = new ModerationQueueAction([], 'create', [
                'data' => \array_merge($data, [
                    'objectTypeID' => $objectTypeID,
                    'objectID' => $user->userID,
                ]),
            ]);
            $objectAction->executeAction();
            $queue = $objectAction->getReturnValues()['returnValues'];
        } else {
            $this->resetAssignments($existingQueue);

            $objectAction = new ModerationQueueAction([$existingQueue->queueID], 'update', [
                'data' => \array_merge($data, [
                    'status' => ModerationQueue::STATUS_OUTSTANDING,
                ]),
            ]);
            $objectAction->executeAction();
            $queue = new ModerationQueue($existingQueue->queueID);
        }

        ModerationQueueManager::getInstance()->resetModerationCount();

        $this->notifyModerators($queue, 'report');
    }

    #[\Override]
    protected function addEntry(int $objectTypeID, int $objectID, int $containerID = 0, array $additionalData = [])
    {
        $existingQueue = $this->getReusableEntry($objectTypeID, $objectID);

        if ($existingQueue === null) {
            $objectAction = new ModerationQueueAction([], 'create', [
                'data' => [
                    'objectTypeID' => $objectTypeID,
                    'objectID' => $objectID,
                    'containerID' => $containerID,
                    'userID' => WCF::getUser()->userID ?: null,
                    'time' => \TIME_NOW,
                    'additionalData' => \serialize($additionalData),
                ],
            ]);
            $objectAction->executeAction();
            $queue = $objectAction->getReturnValues()['returnValues'];
        } else {
            $objectAction = new ModerationQueueAction([$existingQueue->queueID], 'update', [
                'data' => [
                    'status' => ModerationQueue::STATUS_OUTSTANDING,
                    'containerID' => $containerID,
                    'userID' => WCF::getUser()->userID ?: null,
                    'time' => \TIME_NOW,
                    'additionalData' => \serialize(
                        $this->getSpamMarkerPreservingData($existingQueue, $additionalData) ?? $additionalData
                    ),
                ],
            ]);
            $objectAction->executeAction();
            $queue = new ModerationQueue($existingQueue->queueID);
        }

        ModerationQueueManager::getInstance()->resetModerationCount();

        $this->notifyModerators($queue, 'report');

        return $queue;
    }

    /**
     * Removes the user assignments of a closed report that is reused by a system spam report.
     * `notifyModerators()` skips the users that are assigned to an entry, the moderators that
     * handled the closed report would otherwise not learn about the spam report.
     */
    private function resetAssignments(ModerationQueue $queue): void
    {
        $sql = "DELETE FROM wcf1_moderation_queue_to_user
                WHERE       queueID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$queue->queueID]);
    }

    /**
     * Returns the entry for the reported object that is reused by a new report. Entries
     * that are done are kept as they are, a new report creates a new entry instead.
     */
    private function getReusableEntry(int $objectTypeID, int $objectID): ?ModerationQueue
    {
        $sql = "SELECT  *
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status <> ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([
            $objectTypeID,
            $objectID,
            ModerationQueue::STATUS_DONE,
        ]);

        return $statement->fetchObject(ModerationQueue::class);
    }

    /**
     * Adds the detections to an open report, the moderators already know about it.
     *
     * The reason of a report can almost fill the `TEXT` column `additionalData`, adding the
     * detections could then exceed it, which is rejected or truncated by the database. The
     * detections are omitted in that case, the report is still marked as suspected spam. A
     * report without room for even the marker is left unchanged.
     *
     * @param array<string, array<array-key, mixed>> $detections
     */
    private function markOpenReportAsSuspectedSpam(ModerationQueue $queue, array $detections): void
    {
        foreach ([$detections, []] as $storedDetections) {
            $additionalData = \serialize(\array_merge($queue->additionalData, [
                ModerationQueue::SPAM_DETECTIONS_KEY => $storedDetections,
            ]));
            if (\strlen($additionalData) > self::ADDITIONAL_DATA_MAX_BYTES) {
                continue;
            }

            $objectAction = new ModerationQueueAction([$queue->queueID], 'update', [
                'data' => [
                    'additionalData' => $additionalData,
                ],
            ]);
            $objectAction->executeAction();

            ModerationQueueManager::getInstance()->resetModerationCount();

            return;
        }
    }
}
