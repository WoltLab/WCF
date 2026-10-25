<?php

namespace wcf\event\paid\subscription\user;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user has been subscribed to a paid subscription.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PaidSubscriptionUserCreated implements IPsr14Event
{
    public function __construct(
        public readonly PaidSubscriptionUser $subscriptionUser,
        public readonly PaidSubscriptionUserBuilder $builder,
    ) {}
}
