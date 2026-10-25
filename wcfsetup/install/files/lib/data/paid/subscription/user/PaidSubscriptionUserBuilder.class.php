<?php

namespace wcf\data\paid\subscription\user;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting paid subscription users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<PaidSubscriptionUser>
 */
final class PaidSubscriptionUserBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the paid subscription the user is subscribed to.
     */
    public function setSubscription(PaidSubscription $subscription): static
    {
        return $this->setSubscriptionID($subscription->subscriptionID);
    }

    /**
     * Sets the id of the paid subscription the user is subscribed to.
     */
    public function setSubscriptionID(int $subscriptionID): static
    {
        $this->properties['subscriptionID'] = $subscriptionID;

        return $this;
    }

    /**
     * Sets the user who is subscribed to the paid subscription.
     */
    public function setUser(User $user): static
    {
        return $this->setUserID($user->userID);
    }

    /**
     * Sets the id of the user who is subscribed to the paid subscription.
     */
    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the timestamp at which the paid subscription started.
     */
    public function setStartDate(int $startDate): static
    {
        $this->properties['startDate'] = $startDate;

        return $this;
    }

    /**
     * Sets the timestamp at which the paid subscription ends, `0` represents
     * a subscription without an end.
     */
    public function setEndDate(int $endDate): static
    {
        $this->properties['endDate'] = $endDate;

        return $this;
    }

    /**
     * Sets whether the paid subscription is currently active.
     */
    public function setIsActive(bool $isActive): static
    {
        $this->properties['isActive'] = $isActive ? 1 : 0;

        return $this;
    }

    /**
     * Sets whether the user has been notified that the paid subscription is expiring.
     */
    public function setSentExpirationNotification(bool $sentExpirationNotification): static
    {
        $this->properties['sentExpirationNotification'] = $sentExpirationNotification ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['subscriptionID', 'userID'];
    }
}
