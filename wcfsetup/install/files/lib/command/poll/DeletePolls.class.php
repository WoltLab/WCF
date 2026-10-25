<?php

namespace wcf\command\poll;

use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\event\poll\PollsDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes polls including their options and votes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeletePolls
{
    /**
     * @param Poll[] $polls
     */
    public function __construct(
        private readonly array $polls,
    ) {}

    public function __invoke(): void
    {
        if ($this->polls === []) {
            return;
        }

        PollBuilder::deleteAll(\array_values(\array_map(
            static fn(Poll $poll) => $poll->pollID,
            $this->polls
        )));

        EventHandler::getInstance()->fire(new PollsDeleted($this->polls));
    }
}
