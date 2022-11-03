<?php

namespace wcf\system\moderation\queue\command;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ModerationQueueEditor;
use wcf\data\user\User;
use wcf\system\event\EventHandler;
use wcf\system\moderation\queue\event\UserAssigned;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Moderation\Queue\Command
 * @since   6.0
 */
final class AssignUser
{
    private EventHandler $eventHandler;

    private int $moderationQueueId;

    /**
     * The user the queue entry should be assigned to. null to remove the assignment.
     */
    private ?int $userId;

    public function __construct(
        ModerationQueue $moderationQueue,
        ?User $user,
    ) {
        $this->moderationQueueId = $moderationQueue->queueID;
        $this->userId = $user !== null ? $user->userID : null;

        $this->eventHandler = EventHandler::getInstance();
    }

    public function __invoke()
    {
        $moderationQueueEditor = new ModerationQueueEditor(new ModerationQueue($this->moderationQueueId));
        if ($this->userId !== null) {
            $user = new User($this->userId);
        } else {
            $user = null;
        }

        $oldAssignee = $moderationQueueEditor->assignedUserID ? new User($moderationQueueEditor->assignedUserID) : null;

        $data = [
            'assignedUserID' => $user !== null ? $user->userID : null,
        ];

        if ($user !== null) {
            if ($moderationQueueEditor->status == ModerationQueue::STATUS_OUTSTANDING) {
                $data['status'] = ModerationQueue::STATUS_PROCESSING;
            }
        } else {
            if ($moderationQueueEditor->status == ModerationQueue::STATUS_PROCESSING) {
                $data['status'] = ModerationQueue::STATUS_OUTSTANDING;
            }
        }

        $moderationQueueEditor->update($data);

        $this->eventHandler->fire(
            new UserAssigned(
                $moderationQueueEditor->getDecoratedObject(),
                $user,
                $oldAssignee
            )
        );
    }
}
