<?php

namespace wcf\command\poll;

use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\event\poll\PollCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new poll.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreatePoll
{
    public function __construct(
        private readonly PollBuilder $builder,
    ) {}

    public function __invoke(): Poll
    {
        $poll = $this->builder->create();

        EventHandler::getInstance()->fire(new PollCreated($poll, $this->builder));

        return $poll;
    }
}
