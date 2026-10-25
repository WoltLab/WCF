<?php

namespace wcf\event\poll;

use wcf\data\poll\Poll;
use wcf\data\poll\PollBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a poll has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollCreated implements IPsr14Event
{
    public function __construct(
        public readonly Poll $poll,
        public readonly PollBuilder $builder,
    ) {}
}
