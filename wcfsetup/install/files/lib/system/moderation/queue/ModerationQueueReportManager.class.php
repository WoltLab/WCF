<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Moderation queue implementation for reports.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
     * @param string $objectType
     * @param int $objectID
     * @return  bool
     */
    public function isAlreadyReported($objectType, $objectID)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
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
     * @param string $objectType
     * @param int $objectID
     * @return  bool
     */
    public function hasPendingReport($objectType, $objectID)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);

        $sql = "SELECT  COUNT(*)
                FROM    wcf" . WCF_N . "_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status IN (?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
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
     * @param string $objectType
     * @param int $objectID
     * @return  bool
     */
    public function canReport($objectType, $objectID)
    {
        return $this->getProcessor($objectType)->canReport($objectID);
    }

    /**
     * @inheritDoc
     */
    public function getLink($queueID)
    {
        return LinkHandler::getInstance()->getLink('ModerationReport', [
            'id' => $queueID,
            'forceFrontend' => true,
        ]);
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
     * @param string $objectType
     * @param int $objectID
     * @return  \wcf\data\IUserContent
     */
    public function getReportedObject($objectType, $objectID)
    {
        return $this->getProcessor($objectType)->getReportedObject($objectID);
    }

    /**
     * Adds a report for specified content.
     *
     * @param string $objectType
     * @param int $objectID
     * @param string $message
     * @param array $additionalData
     * @throws  InvalidObjectTypeException
     */
    public function addReport($objectType, $objectID, $message, array $additionalData = [])
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
     * @inheritDoc
     */
    protected function addEntry($objectTypeID, $objectID, $containerID = 0, array $additionalData = [])
    {
        $sql = "SELECT  queueID
                FROM    wcf" . WCF_N . "_moderation_queue
                WHERE   objectTypeID = ?
                    AND objectID = ?
                    AND status <> ?";
        $statement = WCF::getDB()->prepareStatement($sql);
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
}
