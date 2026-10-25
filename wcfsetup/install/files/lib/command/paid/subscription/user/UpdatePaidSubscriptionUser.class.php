<?php

namespace wcf\command\paid\subscription\user;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserBuilder;
use wcf\event\paid\subscription\user\PaidSubscriptionUserUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a paid subscription user.
 *
 * Use `ExtendPaidSubscriptionUser`, `RevokePaidSubscriptionUser` or
 * `RestorePaidSubscriptionUser` to change the state of the subscription
 * including the user's group memberships.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdatePaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUserBuilder $builder,
    ) {}

    public function __invoke(): PaidSubscriptionUser
    {
        $subscriptionUser = $this->builder->update();

        EventHandler::getInstance()->fire(new PaidSubscriptionUserUpdated(
            $subscriptionUser,
            $this->builder
        ));

        return $subscriptionUser;
    }
}
