<?php

namespace wcf\command\paid\subscription\user;

use wcf\command\paid\subscription\AddGroupMembership;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserEditor;
use wcf\event\paid\subscription\user\PaidSubscriptionUserRestored;
use wcf\system\event\EventHandler;

/**
 * Restore a paid subscription for a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RestorePaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUser $subscriptionUser,
    ) {}

    public function __invoke(): void
    {
        if ($this->subscriptionUser->isActive) {
            return;
        }

        $user = $this->subscriptionUser->getUser();
        $subscription = $this->subscriptionUser->getSubscription();

        $this->restoreSubscription($this->subscriptionUser);

        (new AddGroupMembership($subscription, $user))();

        $event = new PaidSubscriptionUserRestored($this->subscriptionUser);
        EventHandler::getInstance()->fire($event);
    }

    private function restoreSubscription(PaidSubscriptionUser $subscriptionUser): void
    {
        (new PaidSubscriptionUserEditor($subscriptionUser))->update(['isActive' => 1]);
    }
}
