<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
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

    #[\Override]
    protected function addEntry(int $objectTypeID, int $objectID, int $containerID = 0, array $additionalData = [])
    {
        $sql = "SELECT  queueID
                FROM    wcf1_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status <> ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $objectID,
            ModerationQueue::STATUS_DONE,
        ]);
        $row = $statement->fetchArray();

        if ($row === false) {
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
            $objectAction = new ModerationQueueAction([$row['queueID']], 'update', [
                'data' => [
                    'status' => ModerationQueue::STATUS_OUTSTANDING,
                    'containerID' => $containerID,
                    'userID' => WCF::getUser()->userID ?: null,
                    'time' => \TIME_NOW,
                    'additionalData' => \serialize($additionalData),
                ],
            ]);
            $objectAction->executeAction();
            $queue = new ModerationQueue($row['queueID']);
        }

        ModerationQueueManager::getInstance()->resetModerationCount();

        $this->notifyModerators($queue, 'report');

        return $queue;
    }
}
