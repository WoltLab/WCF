<?php

namespace wcf\command\paid\subscription\user;

use wcf\command\paid\subscription\AddGroupMembership;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserBuilder;
use wcf\event\paid\subscription\user\PaidSubscriptionUserCreated;
use wcf\system\event\EventHandler;
use wcf\util\DateUtil;

/**
 * Subscribes a user to a paid subscription and adds the user to the
 * subscription's user groups.
 *
 * The subscription starts now and its end date is derived from the
 * subscription's length unless the builder provides explicit values.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreatePaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUserBuilder $builder,
    ) {}

    public function __invoke(): PaidSubscriptionUser
    {
        $this->setDefaultDates();

        $subscriptionUser = $this->builder->create();

        new AddGroupMembership(
            $subscriptionUser->getSubscription(),
            $subscriptionUser->getUser()
        )();

        EventHandler::getInstance()->fire(new PaidSubscriptionUserCreated(
            $subscriptionUser,
            $this->builder
        ));

        return $subscriptionUser;
    }

    private function setDefaultDates(): void
    {
        if (!isset($this->builder->properties['startDate'])) {
            $this->builder->setStartDate(\TIME_NOW);
        }

        // A missing subscription id is reported by the builder.
        if (isset($this->builder->properties['endDate']) || !isset($this->builder->properties['subscriptionID'])) {
            return;
        }

        $subscription = new PaidSubscription((int)$this->builder->properties['subscriptionID']);
        if ($subscription->subscriptionLength === 0) {
            $this->builder->setEndDate(0);

            return;
        }

        $d = DateUtil::getDateTimeByTimestamp((int)$this->builder->properties['startDate']);
        $d->add($subscription->getDateInterval());

        $this->builder->setEndDate($d->getTimestamp());
    }
}
