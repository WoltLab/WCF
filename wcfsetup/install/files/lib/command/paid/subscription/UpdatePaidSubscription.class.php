<?php

namespace wcf\command\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionBuilder;
use wcf\event\paid\subscription\PaidSubscriptionUpdated;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a paid subscription.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdatePaidSubscription
{
    public function __construct(
        private readonly PaidSubscriptionBuilder $builder,
    ) {}

    public function __invoke(): PaidSubscription
    {
        $subscription = $this->builder->update();

        // The builder may move the subscription after the object was read.
        $subscription = new PaidSubscription($subscription->subscriptionID);

        PaidSubscriptionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new PaidSubscriptionUpdated(
            $subscription,
            $this->builder
        ));

        return $subscription;
    }
}
