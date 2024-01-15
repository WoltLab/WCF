<?php

namespace wcf\data\moderation\queue;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes moderation queue-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ModerationQueueEditor[]     getObjects()
 * @method  ModerationQueueEditor       getSingleObject()
 */
class ModerationQueueAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ModerationQueueEditor::class;

    /**
     * moderation queue editor object
     * @var ModerationQueueEditor
     */
    public $moderationQueueEditor;

    /**
     * user object
     * @var User
     */
    public $user;

    /**
     * @inheritDoc
     * @return  ModerationQueue
     */
    public function create()
    {
        if (!isset($this->parameters['data']['lastChangeTime'])) {
            $this->parameters['data']['lastChangeTime'] = TIME_NOW;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::create();
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        if (!isset($this->parameters['data']['lastChangeTime'])) {
            $this->parameters['data']['lastChangeTime'] = TIME_NOW;
        }

        parent::update();
    }

    /**
     * Marks a list of objects as done.
     */
    public function markAsDone()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $queueIDs = [];
        foreach ($this->getObjects() as $queue) {
            $queueIDs[] = $queue->queueID;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("queueID IN (?)", [$queueIDs]);

        $sql = "UPDATE  wcf" . WCF_N . "_moderation_queue
                SET     status = " . ModerationQueue::STATUS_DONE . "
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditions->getParameters());

        // reset number of active moderation queue items
        ModerationQueueManager::getInstance()->resetModerationCount();
    }

    /**
     * @since 5.5
     */
    public function validateGetModerationQueueData(): void
    {
        WCF::getSession()->checkPermissions(['mod.general.canUseModeration']);
    }

    /**
     * @since 5.5
     */
    public function getModerationQueueData(): array
    {
        ['queues' => $queues, 'totalCount' => $totalCount] = $this->getModerationQueues();

        $items = \array_map(static function (ViewableModerationQueue $queue) {
            return [
                'content' => StringUtil::encodeHTML($queue->getAffectedObject()->getTitle()),
                'image' => $queue->getIcon()->toHtml(48),
                'isUnread' => $queue->isNew(),
                'link' => $queue->getLink(),
                'objectId' => $queue->queueID,
                'time' => $queue->lastChangeTime,
                'usernames' => [],
            ];
        }, $queues);

        return [
            'items' => $items,
            'totalCount' => $totalCount,
        ];
    }

    /**
     * @since 5.5
     * @deprecated 5.5 This method will be merged with `getModerationQueueData`
     */
    private function getModerationQueues(): array
    {
        // Maximum cardinality of the returned array
        static $MAX_ITEMS = 10;

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("moderation_queue_to_user.userID = ?", [WCF::getUser()->userID]);
        $conditions->add("moderation_queue_to_user.isAffected = ?", [1]);
        $conditions->add(
            "moderation_queue.status IN (?)",
            [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]
        );
        $conditions->add(
            "moderation_queue.time > ?",
            [VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.moderation.queue')]
        );
        $conditions->add("(moderation_queue.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)");

        $sql = "SELECT      moderation_queue.queueID
                FROM        wcf" . WCF_N . "_moderation_queue_to_user moderation_queue_to_user
                LEFT JOIN   wcf" . WCF_N . "_moderation_queue moderation_queue
                ON          moderation_queue.queueID = moderation_queue_to_user.queueID
                LEFT JOIN   wcf" . WCF_N . "_tracked_visit tracked_visit
                ON          tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue') . "
                        AND tracked_visit.objectID = moderation_queue.queueID
                        AND tracked_visit.userID = " . WCF::getUser()->userID . "
                " . $conditions . "
                ORDER BY    moderation_queue.lastChangeTime DESC";
        $statement = WCF::getDB()->prepareStatement($sql, $MAX_ITEMS);
        $statement->execute($conditions->getParameters());
        $queueIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $queues = [];
        if (!empty($queueIDs)) {
            $queueList = new ViewableModerationQueueList();
            $queueList->getConditionBuilder()->add("moderation_queue.queueID IN (?)", [$queueIDs]);
            $queueList->sqlOrderBy = "moderation_queue.lastChangeTime DESC";
            $queueList->readObjects();
            foreach ($queueList as $queue) {
                $queues[] = $queue;
            }
        }

        // check if user storage is outdated
        $totalCount = ModerationQueueManager::getInstance()->getUnreadModerationCount();
        $count = \count($queues);
        if ($count < $MAX_ITEMS) {
            // load more entries to fill up list
            $queueList = new ViewableModerationQueueList();
            $queueList->getConditionBuilder()->add(
                "moderation_queue.status IN (?)",
                [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]
            );
            if (!empty($queueIDs)) {
                $queueList->getConditionBuilder()->add("moderation_queue.queueID NOT IN (?)", [$queueIDs]);
            }
            $queueList->sqlOrderBy = "moderation_queue.lastChangeTime DESC";
            $queueList->sqlLimit = $MAX_ITEMS - $count;
            $queueList->readObjects();
            foreach ($queueList as $queue) {
                $queues[] = $queue;
            }

            // check if stored count is out of sync
            if ($count < $totalCount) {
                UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');

                // check for orphaned queues
                $queueCount = ModerationQueueManager::getInstance()->getUnreadModerationCount();
                if (\count($queues) < $queueCount) {
                    ModerationQueueManager::getInstance()->identifyOrphans();
                }
            }
        }

        return [
            'queues' => $queues,
            'totalCount' => $totalCount,
        ];
    }

    /**
     * Marks queue entries as read.
     */
    public function markAsRead()
    {
        if (empty($this->parameters['visitTime'])) {
            $this->parameters['visitTime'] = TIME_NOW;
        }

        if (empty($this->objects)) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $queue) {
            VisitTracker::getInstance()->trackObjectVisit(
                'com.woltlab.wcf.moderation.queue',
                $queue->queueID,
                $this->parameters['visitTime']
            );
        }

        // reset storage
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');

        if (\count($this->objects) == 1) {
            $queue = \reset($this->objects);

            return [
                'markAsRead' => $queue->queueID,
                'totalCount' => ModerationQueueManager::getInstance()->getUnreadModerationCount(true),
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function validateMarkAsRead()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $queue) {
            if (!$queue->canEdit()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * Marks all queue entries as read.
     */
    public function markAllAsRead()
    {
        VisitTracker::getInstance()->trackTypeVisit('com.woltlab.wcf.moderation.queue');

        // reset storage
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadModerationCount');

        return [
            'markAllAsRead' => true,
        ];
    }

    /**
     * Validates the mark all as read action.
     */
    public function validateMarkAllAsRead()
    {
        // does nothing
    }

    /**
     * Validates the `assignUserByClipboard` action.
     *
     * @since   5.4
     */
    public function validateAssignUserByClipboard(): void
    {
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $moderationQueueEditor) {
            if (!$moderationQueueEditor->canEdit()) {
                throw new PermissionDeniedException();
            }
        }

        $this->readInteger('assignedUserID', false);

        if ($this->parameters['assignedUserID'] < -1) {
            throw new UserInputException('assignedUserID');
        } elseif ($this->parameters['assignedUserID'] == -1) {
            $this->readString('assignedUsername', false);

            $this->user = User::getUserByUsername($this->parameters['assignedUsername']);
            if (!$this->user->userID) {
                throw new UserInputException('assignedUsername', 'notFound');
            }

            foreach ($this->getObjects() as $moderationQueueEditor) {
                /** @var IModerationQueueHandler $processor */
                $processor = ObjectTypeCache::getInstance()->getObjectType(
                    $moderationQueueEditor->objectTypeID
                )->getProcessor();

                $isAffected = $processor->isAffectedUser(
                    $moderationQueueEditor->getDecoratedObject(),
                    $this->user->userID
                );
                if (!$isAffected) {
                    throw new UserInputException('assignedUsername', 'notAffected');
                }
            }

            $this->parameters['assignedUserID'] = $this->user->userID;
            $this->parameters['assignedUsername'] = '';
        } elseif ($this->parameters['assignedUserID'] == WCF::getUser()->userID) {
            $this->user = WCF::getUser();
        }
    }

    /**
     * Assigns a user to multiple moderation queue entries via clipboard.
     *
     * @since   5.4
     */
    public function assignUserByClipboard(): void
    {
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $moderationQueueEditor) {
            $data = [
                'assignedUserID' => $this->user ? $this->user->userID : null,
            ];
            if ($this->user) {
                if ($moderationQueueEditor->status == ModerationQueue::STATUS_OUTSTANDING) {
                    $data['status'] = ModerationQueue::STATUS_PROCESSING;
                }
            } else {
                if ($moderationQueueEditor->status == ModerationQueue::STATUS_PROCESSING) {
                    $data['status'] = ModerationQueue::STATUS_OUTSTANDING;
                }
            }

            $moderationQueueEditor->update($data);
        }
        WCF::getDB()->commitTransaction();

        $this->unmarkItems();
    }

    /**
     * Unmarks the moderation queue entries with the given ids or all currently handled entries if
     * no argument is given.
     *
     * @param   int[]   $queueIDs
     * @since   5.4
     */
    protected function unmarkItems(array $queueIDs = []): void
    {
        if (empty($queueIDs)) {
            $queueIDs = $this->objectIDs;
        }

        if (!empty($queueIDs)) {
            ClipboardHandler::getInstance()->unmark(
                $queueIDs,
                ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.moderation.queue')
            );
        }
    }
}
