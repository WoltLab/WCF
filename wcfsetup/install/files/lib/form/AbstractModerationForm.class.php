<?php

namespace wcf\form;

use wcf\data\comment\StructuredCommentList;
use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;

/**
 * Provides an abstract form for moderation queue processing.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Form
 */
abstract class AbstractModerationForm extends AbstractForm
{
    /**
     * id of the assigned user
     * @var int
     */
    public $assignedUserID = 0;

    /**
     * data used for moderation queue update
     * @var array
     */
    public $data = [];

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.general.canUseModeration'];

    /**
     * moderation queue object
     * @var ViewableModerationQueue
     */
    public $queue;

    /**
     * queue id
     * @var int
     */
    public $queueID = 0;

    /**
     * comment object type id
     * @var int
     */
    public $commentObjectTypeID = 0;

    /**
     * comment manager object
     * @var ICommentManager
     */
    public $commentManager;

    /**
     * list of comments
     * @var StructuredCommentList
     */
    public $commentList;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        // if the moderation queue entry has been created after the user visited the
        // site the last time, they have not been assigned to the queue entry yet,
        // thus `ViewableModerationQueue::getViewableModerationQueue()` will always
        // return `null`; `ModerationQueueManager::getOutstandingModerationCount()`
        // internally refreshes the user assignments if necessary so that the
        // `ViewableModerationQueue::getViewableModerationQueue()` call will be successful
        ModerationQueueManager::getInstance()->getOutstandingModerationCount();

        if (isset($_REQUEST['id'])) {
            $this->queueID = (int)$_REQUEST['id'];
        }
        $this->queue = ViewableModerationQueue::getViewableModerationQueue($this->queueID);
        if ($this->queue === null) {
            throw new IllegalLinkException();
        }

        if (!$this->queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();
        if ($this->getPsr7Response()) {
            return;
        }

        if (empty($_POST)) {
            $this->assignedUserID = $this->queue->assignedUserID;
        }

        PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.ModerationList');

        $this->commentObjectTypeID = CommentHandler::getInstance()
            ->getObjectTypeID('com.woltlab.wcf.moderation.queue');
        $this->commentManager = CommentHandler::getInstance()
            ->getObjectType($this->commentObjectTypeID)
            ->getProcessor();
        $this->commentList = CommentHandler::getInstance()
            ->getCommentList($this->commentManager, $this->commentObjectTypeID, $this->queueID);

        // update queue visit
        if ($this->queue->isNew()) {
            $action = new ModerationQueueAction([$this->queue->getDecoratedObject()], 'markAsRead', [
                'visitTime' => TIME_NOW,
            ]);
            $action->executeAction();
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'assignedUserID' => $this->assignedUserID,
            'queue' => $this->queue,
            'queueID' => $this->queueID,
            'commentCanAdd' => true,
            'commentList' => $this->commentList,
            'commentObjectTypeID' => $this->commentObjectTypeID,
            'lastCommentTime' => $this->commentList ? $this->commentList->getMinCommentTime() : 0,
        ]);
    }

    /**
     * Prepares update of moderation queue item.
     */
    protected function prepareSave()
    {
        EventHandler::getInstance()->fireAction($this, 'prepareSave');
    }
}
