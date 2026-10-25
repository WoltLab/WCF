<?php

namespace wcf\event\poll;

use wcf\data\poll\Poll;
use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user has voted in a poll or changed their vote.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollVoted implements IPsr14Event
{
    /**
     * @param list<int> $optionIDs ids of the selected options
     */
    public function __construct(
        public readonly Poll $poll,
        public readonly User $user,
        public readonly array $optionIDs,
    ) {}
}
