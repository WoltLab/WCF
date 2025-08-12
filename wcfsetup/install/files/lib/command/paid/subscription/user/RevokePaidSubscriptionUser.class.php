<?php

namespace wcf\command\paid\subscription\user;

use wcf\command\paid\subscription\AddGroupMembership;
use wcf\command\paid\subscription\RemoveGroupMembership;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserEditor;
use wcf\event\paid\subscription\user\PaidSubscriptionUserRevoked;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Revokes a paid subscription for a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RevokePaidSubscriptionUser
{
    public function __construct(
        private readonly PaidSubscriptionUser $subscriptionUser,
    ) {}

    public function __invoke(): void
    {
        $this->revokeSubscription($this->subscriptionUser);
        $user = $this->subscriptionUser->getUser();

        (new RemoveGroupMembership($this->subscriptionUser->getSubscription(), $user))();

        foreach ($this->getActiveSubscriptions($this->subscriptionUser->userID) as $activeSubscription) {
            (new AddGroupMembership($activeSubscription, $user))();
        }

        $event = new PaidSubscriptionUserRevoked($this->subscriptionUser);
        EventHandler::getInstance()->fire($event);
    }

    private function revokeSubscription(PaidSubscriptionUser $subscriptionUser): void
    {
        (new PaidSubscriptionUserEditor($subscriptionUser))->update(['isActive' => 0]);
    }

    /**
     * @return PaidSubscription[]
     */
    private function getActiveSubscriptions(int $userID): array
    {
        $sql = "SELECT *
                FROM   wcf1_paid_subscription
                WHERE  subscriptionID IN (
                    SELECT subscriptionID
                    FROM   wcf1_paid_subscription_user
                    WHERE  userID = ?
                       AND isActive = ?
                )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $userID,
            1,
        ]);

        return $statement->fetchObjects(PaidSubscription::class);
    }
}
