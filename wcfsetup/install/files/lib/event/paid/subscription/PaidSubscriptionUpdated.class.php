<?php

namespace wcf\event\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a paid subscription has been updated.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PaidSubscriptionUpdated implements IPsr14Event
{
    public function __construct(
        public readonly PaidSubscription $subscription,
        public readonly PaidSubscriptionBuilder $builder,
    ) {}
}
