<?php

namespace wcf\data\paid\subscription\transaction\log;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting paid subscription transaction log entries.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<PaidSubscriptionTransactionLog>
 */
final class PaidSubscriptionTransactionLogBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the paid subscription-user-association the log entry belongs to.
     */
    public function setSubscriptionUser(?PaidSubscriptionUser $subscriptionUser): static
    {
        return $this->setSubscriptionUserID($subscriptionUser?->subscriptionUserID);
    }

    /**
     * Sets the id of the paid subscription-user-association the log entry belongs to.
     */
    public function setSubscriptionUserID(?int $subscriptionUserID): static
    {
        $this->properties['subscriptionUserID'] = $subscriptionUserID;

        return $this;
    }

    /**
     * Sets the user who caused the log entry.
     */
    public function setUser(?User $user): static
    {
        return $this->setUserID($user?->userID);
    }

    /**
     * Sets the id of the user who caused the log entry.
     */
    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the paid subscription the log entry belongs to.
     */
    public function setSubscription(?PaidSubscription $subscription): static
    {
        return $this->setSubscriptionID($subscription?->subscriptionID);
    }

    /**
     * Sets the id of the paid subscription the log entry belongs to.
     */
    public function setSubscriptionID(?int $subscriptionID): static
    {
        $this->properties['subscriptionID'] = $subscriptionID;

        return $this;
    }

    /**
     * Sets the id of the `com.woltlab.wcf.payment.method` object type.
     */
    public function setPaymentMethodObjectTypeID(int $paymentMethodObjectTypeID): static
    {
        $this->properties['paymentMethodObjectTypeID'] = $paymentMethodObjectTypeID;

        return $this;
    }

    /**
     * Sets the timestamp at which the log entry has been created.
     */
    public function setLogTime(int $logTime): static
    {
        $this->properties['logTime'] = $logTime;

        return $this;
    }

    /**
     * Sets the identifier of the transaction.
     */
    public function setTransactionID(string $transactionID): static
    {
        $this->properties['transactionID'] = \mb_substr($transactionID, 0, 255);

        return $this;
    }

    /**
     * Sets the details of the transaction.
     *
     * @param ?mixed[] $transactionDetails
     */
    public function setTransactionDetails(?array $transactionDetails): static
    {
        $this->properties['transactionDetails'] = $transactionDetails !== null ? \serialize($transactionDetails) : null;

        return $this;
    }

    /**
     * Sets the log message describing the status of the transaction.
     */
    public function setLogMessage(string $logMessage): static
    {
        $this->properties['logMessage'] = \mb_substr($logMessage, 0, 255);

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['paymentMethodObjectTypeID', 'logTime', 'transactionID'];
    }
}
