<?php

namespace wcf\event\user\object\watch;

use wcf\data\user\object\watch\UserObjectWatch;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user has subscribed to a watchable object.
 *
 * This event is only fired for new subscriptions, changing the notification
 * setting of an existing subscription does not fire it again.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ObjectSubscribed implements IPsr14Event
{
    public function __construct(
        public readonly UserObjectWatch $userObjectWatch,
    ) {}
}
