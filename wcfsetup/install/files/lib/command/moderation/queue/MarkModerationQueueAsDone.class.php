<?php

namespace wcf\command\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Marks a moderation queue as done.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkModerationQueueAsDone
{
    public function __construct(public readonly ModerationQueue $queue) {}

    public function __invoke(): void
    {
        $editor = new ModerationQueueEditor($this->queue);
        $editor->update([
            'status' => ModerationQueue::STATUS_DONE,
        ]);

        ModerationQueueManager::getInstance()->resetModerationCount();
    }
}
