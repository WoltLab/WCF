<?php

namespace wcf\event\paid\subscription\user;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\event\IPsr14Event;

/**
 * Indicates that a paid subscription user has been extended.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PaidSubscriptionUserExtend implements IPsr14Event
{
    public function __construct(public readonly PaidSubscriptionUser $subscriptionUser, int $endDate) {}
}
