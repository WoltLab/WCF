<?php

namespace wcf\command\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\user\User;
use wcf\event\moderation\queue\UserAssigned;
use wcf\system\event\EventHandler;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AssignUser
{
    public function __construct(
        private readonly ModerationQueue $moderationQueue,
        private readonly ?User $user,
    ) {}

    public function __invoke(): void
    {
        $moderationQueueEditor = new ModerationQueueEditor($this->moderationQueue);
        $oldAssignee = $moderationQueueEditor->assignedUserID ? new User($moderationQueueEditor->assignedUserID) : null;

        // If the old assignee matches the new assignee, we do not need to
        // do anything.
        if ($oldAssignee?->userID === $this->user?->userID) {
            return;
        }

        $data = [
            'assignedUserID' => $this->user?->userID,
        ];

        if ($this->user !== null) {
            if ($moderationQueueEditor->status == ModerationQueue::STATUS_OUTSTANDING) {
                $data['status'] = ModerationQueue::STATUS_PROCESSING;
            }
        } else {
            if ($moderationQueueEditor->status == ModerationQueue::STATUS_PROCESSING) {
                $data['status'] = ModerationQueue::STATUS_OUTSTANDING;
            }
        }

        $moderationQueueEditor->update($data);

        EventHandler::getInstance()->fire(
            new UserAssigned(
                $moderationQueueEditor->getDecoratedObject(),
                $this->user,
                $oldAssignee
            )
        );
    }
}
