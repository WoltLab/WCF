<?php

namespace wcf\command\paid\subscription\user;

use wcf\command\paid\subscription\AddGroupMembership;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserEditor;
use wcf\event\paid\subscription\user\PaidSubscriptionUserExtend;
use wcf\system\event\EventHandler;
use wcf\util\DateUtil;

/**
 * Extends the period of a paid subscription for a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ExtendPaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUser $subscriptionUser,
        private readonly ?int $endDate = null,
    ) {}

    public function __invoke(): void
    {
        $endDate = $this->getEndDate($this->subscriptionUser->getSubscription());

        $this->extendSubscription($this->subscriptionUser, $endDate);

        if (!$this->subscriptionUser->isActive) {
            (new AddGroupMembership(
                $this->subscriptionUser->getSubscription(),
                $this->subscriptionUser->getUser()
            ))();
        }

        $event = new PaidSubscriptionUserExtend($this->subscriptionUser, $endDate);
        EventHandler::getInstance()->fire($event);
    }

    private function getEndDate(PaidSubscription $subscription): int
    {
        if ($this->endDate === null) {
            if (!$subscription->subscriptionLength) {
                return 0;
            }

            $d = DateUtil::getDateTimeByTimestamp(TIME_NOW);
            $d->add($subscription->getDateInterval());

            return $d->getTimestamp();
        }

        return $this->endDate;
    }

    private function extendSubscription(PaidSubscriptionUser $subscriptionUser, int $endDate): void
    {
        (new PaidSubscriptionUserEditor($subscriptionUser))->update([
            'endDate' => $endDate,
            'isActive' => 1,
            'sentExpirationNotification' => 0,
        ]);
    }
}
