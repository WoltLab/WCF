<?php

namespace wcf\command\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionEditor;
use wcf\event\paid\subscription\PaidSubscriptionDisabled;
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
        if ($this->subscription->isDisabled) {
            return;
        }

        (new PaidSubscriptionEditor($this->subscription))->update([
            'isDisabled' => 1,
        ]);

        $event = new PaidSubscriptionDisabled($this->subscription);
        EventHandler::getInstance()->fire($event);
    }
}
