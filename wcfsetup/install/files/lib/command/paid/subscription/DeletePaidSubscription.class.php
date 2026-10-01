<?php

namespace wcf\command\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionBuilder;
use wcf\event\paid\subscription\PaidSubscriptionDeleted;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Deletes a paid subscription.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeletePaidSubscription
{
    public function __construct(
        private readonly PaidSubscription $subscription,
    ) {}

    public function __invoke(): void
    {
        // The given object may predate the deletion of another subscription,
        // which moved this one to a lower position.
        $showOrder = (new PaidSubscription($this->subscription->subscriptionID))->showOrder;

        PaidSubscriptionBuilder::delete($this->subscription);

        $sql = "UPDATE  wcf1_paid_subscription
                SET     showOrder = showOrder - 1
                WHERE   showOrder > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$showOrder]);

        PaidSubscriptionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new PaidSubscriptionDeleted($this->subscription));
    }
}
