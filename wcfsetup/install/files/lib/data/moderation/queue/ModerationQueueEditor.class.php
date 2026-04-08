<?php

namespace wcf\data\moderation\queue;

use wcf\data\DatabaseObjectEditor;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Extends the moderation queue object with functions to create, update and delete queue entries.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       ModerationQueue
 * @extends DatabaseObjectEditor<ModerationQueue>
 */
class ModerationQueueEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModerationQueue::class;

    /**
     * Marks this entry as confirmed, e.g. report was justified and content was deleted or
     * content was approved.
     *
     * @return void
     */
    public function markAsConfirmed()
    {
        $this->update(['status' => ModerationQueue::STATUS_CONFIRMED]);

        // reset moderation count
        ModerationQueueManager::getInstance()->resetModerationCount();
    }

    /**
     * Marks this entry as rejected, e.g. report was unjustified or content approval was denied.
     *
     * @return void
     */
    public function markAsRejected(bool $markAsJustified = false)
    {
        $data = ['status' => ModerationQueue::STATUS_REJECTED];
        if ($markAsJustified) {
            $additionalData = $this->getDecoratedObject()->additionalData;
            $additionalData['markAsJustified'] = true;

            $data['additionalData'] = \serialize($additionalData);
        }

        $this->update($data);

        // reset moderation count
        ModerationQueueManager::getInstance()->resetModerationCount();
    }

    /**
     * Marks this entry as in progress.
     *
     * @return void
     */
    public function markAsInProgress()
    {
        $this->update(['status' => ModerationQueue::STATUS_PROCESSING]);
    }
}
