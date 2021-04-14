<?php

namespace wcf\system\clipboard\action;

use wcf\data\moderation\queue\ModerationQueueAction;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Clipboard action implementation for moderation queue entries.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Clipboard\Action
 * @since   5.4
 */
class ModerationQueueClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $supportedActions = [
        'assignUserByClipboard',
    ];

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return ModerationQueueAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.moderation.queue';
    }

    /**
     * Returns the ids of the ids of the marked moderation queue entries for which the active user
     * can assign users.
     *
     * @return  int[]
     */
    public function validateAssignUserByClipboard(): array
    {
        return \array_values(\array_filter(\array_map(static function (ViewableModerationQueue $queue) {
            if ($queue->canEdit()) {
                return $queue->queueID;
            }
        }, $this->objects)));
    }
}
