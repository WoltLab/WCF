<?php

namespace wcf\event\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\event\IPsr14Event;

/**
 * Indicates that a paid subscription has been disabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PaidSubscriptionDisabled implements IPsr14Event
{
    public function __construct(public readonly PaidSubscription $subscription) {}
}
