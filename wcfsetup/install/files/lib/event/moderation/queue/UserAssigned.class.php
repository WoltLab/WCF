<?php

namespace wcf\event\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user was assigned or reassigned to a moderation queue entry.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserAssigned extends \wcf\system\moderation\queue\event\UserAssigned implements IPsr14Event
{
    private int $moderationQueueId;

    private ?int $newAssigneeId;

    private ?int $oldAssigneeId;

    public function __construct(
        ModerationQueue $moderationQueue,
        ?User $newAssignee,
        ?User $oldAssignee,
    ) {
        $this->moderationQueueId = $moderationQueue->queueID;
        $this->newAssigneeId = $newAssignee?->userID;
        $this->oldAssigneeId = $oldAssignee?->userID;
    }

    public function getModerationQueue(): ModerationQueue
    {
        return new ModerationQueue($this->moderationQueueId);
    }

    public function getNewAssignee(): ?User
    {
        return $this->newAssigneeId !== null ? new User($this->newAssigneeId) : null;
    }

    public function getOldAssignee(): ?User
    {
        return $this->oldAssigneeId !== null ? new User($this->oldAssigneeId) : null;
    }
}
