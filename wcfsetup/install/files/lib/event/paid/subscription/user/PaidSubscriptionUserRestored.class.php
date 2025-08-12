<?php

namespace wcf\event\paid\subscription\user;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\event\IPsr14Event;

/**
 * Indicates that a paid subscription for a user has been restored.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PaidSubscriptionUserRestored implements IPsr14Event
{
    public function __construct(public readonly PaidSubscriptionUser $subscriptionUser) {}
}
