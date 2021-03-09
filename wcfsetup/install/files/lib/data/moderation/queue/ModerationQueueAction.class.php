<?php

namespace wcf\data\moderation\queue;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes moderation queue-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Moderation\Queue
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
     * Validates parameters to fetch a list of outstanding queues.
     */
    public function validateGetOutstandingQueues()
    {
        WCF::getSession()->checkPermissions(['mod.general.canUseModeration']);
    }

    /**
     * Returns a list of outstanding queues.
     *
     * @return  string[]
     */
    public function getOutstandingQueues()
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
                UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'outstandingModerationCount');

                // check for orphaned queues
                $queueCount = ModerationQueueManager::getInstance()->getUnreadModerationCount();
                if (\count($queues) < $queueCount) {
                    ModerationQueueManager::getInstance()->identifyOrphans();
                }
            }
        }

        WCF::getTPL()->assign([
            'queues' => $queues,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('moderationQueueList'),
            'totalCount' => $totalCount,
        ];
    }

    /**
     * Validates parameters to show the user assign form.
     */
    public function validateGetAssignUserForm()
    {
        $this->moderationQueueEditor = $this->getSingleObject();

        // check if queue is accessible for current user
        if (!$this->moderationQueueEditor->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Returns the user assign form.
     *
     * @return  string[]
     */
    public function getAssignUserForm()
    {
        $assignedUser = $this->moderationQueueEditor->assignedUserID ? new User($this->moderationQueueEditor->assignedUserID) : null;

        WCF::getTPL()->assign([
            'assignedUser' => $assignedUser,
            'queue' => $this->moderationQueueEditor,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('moderationQueueAssignUser'),
        ];
    }

    /**
     * Validates parameters to assign a user.
     */
    public function validateAssignUser()
    {
        // To assign a user we need to be able to fetch the assignment form.
        $this->validateGetAssignUserForm();

        $this->readInteger('assignedUserID', true);

        if ($this->parameters['assignedUserID'] && $this->parameters['assignedUserID'] != -1) {
            if ($this->parameters['assignedUserID'] != WCF::getUser()->userID && $this->parameters['assignedUserID'] != $this->moderationQueueEditor->assignedUserID) {
                // user id is either faked or changed during viewing, use database value instead
                $this->parameters['assignedUserID'] = $this->moderationQueueEditor->assignedUserID;
            }
        }

        if ($this->parameters['assignedUserID'] == -1) {
            $this->readString('assignedUsername');

            $this->user = User::getUserByUsername($this->parameters['assignedUsername']);
            if (!$this->user->userID) {
                throw new UserInputException('assignedUsername', 'notFound');
            }

            // get handler
            $objectType = ObjectTypeCache::getInstance()->getObjectType($this->moderationQueueEditor->objectTypeID);
            if (
                !$objectType->getProcessor()->isAffectedUser(
                    $this->moderationQueueEditor->getDecoratedObject(),
                    $this->user->userID
                )
            ) {
                throw new UserInputException('assignedUsername', 'notAffected');
            }

            $this->parameters['assignedUserID'] = $this->user->userID;
            $this->parameters['assignedUsername'] = '';
        } else {
            $this->user = new User($this->parameters['assignedUserID']);
        }
    }

    /**
     * Returns the data for the newly assigned user.
     *
     * @return  string[]
     */
    public function assignUser()
    {
        $data = ['assignedUserID' => $this->parameters['assignedUserID'] ?: null];
        if ($this->user->userID) {
            if ($this->moderationQueueEditor->status == ModerationQueue::STATUS_OUTSTANDING) {
                $data['status'] = ModerationQueue::STATUS_PROCESSING;
            }
        } else {
            if ($this->moderationQueueEditor->status == ModerationQueue::STATUS_PROCESSING) {
                $data['status'] = ModerationQueue::STATUS_OUTSTANDING;
            }
        }

        $this->moderationQueueEditor->update($data);

        $username = $this->user->userID ? $this->user->username : WCF::getLanguage()->get('wcf.moderation.assignedUser.nobody');
        $link = '';
        if ($this->user->userID) {
            $link = $this->user->getLink();
        }

        $newStatus = '';
        if (isset($data['status'])) {
            $newStatus = ($data['status'] == ModerationQueue::STATUS_OUTSTANDING) ? 'outstanding' : 'processing';
        }

        return [
            'link' => $link,
            'newStatus' => $newStatus,
            'userID' => $this->user->userID,
            'username' => $username,
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
}
