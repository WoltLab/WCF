<?php

namespace wcf\command\paid\subscription\user;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserBuilder;
use wcf\event\paid\subscription\user\PaidSubscriptionUserDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes a paid subscription user, an active subscription is revoked first.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeletePaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUser $subscriptionUser,
    ) {}

    public function __invoke(): void
    {
        if ($this->subscriptionUser->isActive !== 0) {
            new RevokePaidSubscriptionUser($this->subscriptionUser)();
        }

        PaidSubscriptionUserBuilder::delete($this->subscriptionUser);

        EventHandler::getInstance()->fire(new PaidSubscriptionUserDeleted($this->subscriptionUser));
    }
}
