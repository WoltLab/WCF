<?php

namespace wcf\event\user\object\watch;

use wcf\data\user\object\watch\UserObjectWatch;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user has unsubscribed from a watchable object.
 *
 * The subscription referenced by this event has already been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ObjectUnsubscribed implements IPsr14Event
{
    public function __construct(
        public readonly UserObjectWatch $userObjectWatch,
    ) {}
}
