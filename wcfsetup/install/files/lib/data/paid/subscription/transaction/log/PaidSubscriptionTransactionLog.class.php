<?php

namespace wcf\data\paid\subscription\transaction\log;

use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Represents a paid subscription transaction log entry.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $logID              unique id of the paid subscription transaction log entry
 * @property-read   int|null $subscriptionUserID     id of the paid subscription-user-association or `null` if no such association exists
 * @property-read   int|null $userID             id of the user who caused the paid subscription transaction log entry or `null` if the user does not exist anymore
 * @property-read   int $subscriptionID         id of the paid subscription
 * @property-read   int $paymentMethodObjectTypeID  id of the `com.woltlab.wcf.payment.method` object type
 * @property-read   int $logTime            timestamp at which the log has been created
 * @property-read   string $transactionID          identifier of the paid subscription transaction
 * @property-read   string $transactionDetails     serialized details of the paid subscription transaction
 * @property-read   string $logMessage         log message describing the status of the paid subscription transaction
 */
class PaidSubscriptionTransactionLog extends DatabaseObject
{
    /**
     * user object
     * @var User
     */
    protected $user;

    /**
     * paid subscription object
     * @var PaidSubscription
     */
    protected $subscription;

    /**
     * Returns the payment method of this transaction.
     *
     * @return  string
     */
    public function getPaymentMethodName()
    {
        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->paymentMethodObjectTypeID);

        return $objectType->objectType;
    }

    /**
     * Returns transaction details.
     *
     * @return  array
     */
    public function getTransactionDetails()
    {
        return \unserialize($this->transactionDetails);
    }

    /**
     * Returns the user of this transaction.
     *
     * @return  User
     */
    public function getUser()
    {
        if ($this->user === null) {
            $this->user = new User($this->userID);
        }

        return $this->user;
    }

    /**
     * Returns the paid subscription of this transaction.
     *
     * @return  PaidSubscription
     */
    public function getSubscription()
    {
        if ($this->subscription === null) {
            $this->subscription = new PaidSubscription($this->subscriptionID);
        }

        return $this->subscription;
    }

    /**
     * Returns the transaction log entry by transaction id or `null` if no such entry exists.
     *
     * @param int $paymentMethodObjectTypeID
     * @param string $transactionID
     * @return  PaidSubscriptionTransactionLog|null
     */
    public static function getLogByTransactionID($paymentMethodObjectTypeID, $transactionID)
    {
        $sql = "SELECT  *
                FROM    wcf1_paid_subscription_transaction_log
                WHERE   paymentMethodObjectTypeID = ?
                    AND transactionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$paymentMethodObjectTypeID, $transactionID]);
        $row = $statement->fetchArray();
        if ($row !== false) {
            return new self(null, $row);
        }

        return null;
    }
}
