<?php

namespace wcf\event\poll;

use wcf\data\poll\Poll;
use wcf\event\IPsr14Event;

/**
 * Indicates that polls have been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollsDeleted implements IPsr14Event
{
    /**
     * @param Poll[] $polls
     */
    public function __construct(
        public readonly array $polls,
    ) {}
}
