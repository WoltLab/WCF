<?php

namespace wcf\form;

use wcf\command\moderation\queue\MarkModerationQueueAsRead;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\page\ModerationListPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\interaction\user\ModerationQueueInteractions;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\view\CommentsView;
use wcf\system\WCF;

/**
 * Provides an abstract form for moderation queue processing.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
     * @var mixed[]
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

    public CommentsView $commentsView;

    #[\Override]
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
            $this->queueID = \intval($_REQUEST['id']);
        }
        $this->queue = ViewableModerationQueue::getViewableModerationQueue($this->queueID);
        if ($this->queue === null) {
            throw new IllegalLinkException();
        }

        if (!$this->queue->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
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

        $this->loadComments();

        // update queue visit
        if ($this->queue->isNew()) {
            (new MarkModerationQueueAsRead($this->queue->getDecoratedObject()))();
        }
    }

    /**
     * @since 6.3
     */
    protected function loadComments(): void
    {
        $this->commentsView = new CommentsView(
            'com.woltlab.wcf.moderation.queue',
            $this->queueID,
            'moderationQueueCommentList',
            true,
            $this->queue->comments,
            sectionDescription: WCF::getLanguage()->getDynamicVariable('wcf.moderation.comments.description')
        );
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'assignedUserID' => $this->assignedUserID,
            'queue' => $this->queue,
            'queueID' => $this->queueID,
            'commentsView' => $this->commentsView,
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentInteractionButton(
                new ModerationQueueInteractions(),
                $this->queue,
                LinkHandler::getInstance()->getControllerLink(ModerationListPage::class),
                WCF::getLanguage()->getDynamicVariable('wcf.moderation.edit.button', [
                    'queue' => $this->queue,
                ]),
                "core/moderation-queues/{$this->queue->queueID}/content-header-title"
            ),
        ]);
    }

    /**
     * Prepares update of moderation queue item.
     *
     * @return void
     */
    protected function prepareSave()
    {
        EventHandler::getInstance()->fireAction($this, 'prepareSave');
    }
}
