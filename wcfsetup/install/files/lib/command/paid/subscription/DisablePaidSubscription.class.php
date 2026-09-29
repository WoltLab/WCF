<?php

namespace wcf\command\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionBuilder;
use wcf\event\paid\subscription\PaidSubscriptionDisabled;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Disables a paid subscription.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisablePaidSubscription
{
    public function __construct(private readonly PaidSubscription $subscription) {}

    public function __invoke(): void
    {
        PaidSubscriptionBuilder::forUpdate($this->subscription)
            ->setIsDisabled(true)
            ->update();

        PaidSubscriptionCacheBuilder::getInstance()->reset();

        $event = new PaidSubscriptionDisabled($this->subscription);
        EventHandler::getInstance()->fire($event);
    }
}
