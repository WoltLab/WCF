<?php
namespace wcf\data\paid\subscription\transaction\log;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a paid subscription transaction log entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\Transaction\Log
 * 
 * @property-read	integer		$logID				unique id of the paid subscription transaction log entry
 * @property-read	integer|null	$subscriptionUserID		id of the paid subscription-user-association or `null` if no such association exists
 * @property-read	integer|null	$userID				id of the user who caused the paid subscription transaction log entry or `null` if the user does not exist anymore
 * @property-read	integer		$subscriptionID			id of the paid subscription
 * @property-read	integer		$paymentMethodObjectTypeID	id of the `com.woltlab.wcf.payment.method` object type
 * @property-read	integer		$logTime			timestamp at which the log has been created
 * @property-read	string		$transactionID			identifier of the paid subscription transaction
 * @property-read	string		$transactionDetails		serialized details of the paid subscription transaction
 * @property-read	string		$logMessage			log message describing the status of the paid subscription transaction
 */
class PaidSubscriptionTransactionLog extends DatabaseObject {
	/**
	 * user object
	 * @var	User
	 */
	protected $user = null;
	
	/**
	 * paid subscription object
	 * @var	PaidSubscription
	 */
	protected $subscription = null;
	
	/**
	 * Returns the payment method of this transaction.
	 * 
	 * @return	string
	 */
	public function getPaymentMethodName() {
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->paymentMethodObjectTypeID);
		return $objectType->objectType;
	}
	
	/**
	 * Returns transaction details.
	 * 
	 * @return	array
	 */
	public function getTransactionDetails() {
		return unserialize($this->transactionDetails);
	}
	
	/**
	 * Returns the user of this transaction.
	 * 
	 * @return	User
	 */
	public function getUser() {
		if ($this->user === null) {
			$this->user = new User($this->userID);
		}
		
		return $this->user;
	}
	
	/**
	 * Returns the paid subscription of this transaction.
	 * 
	 * @return	PaidSubscription
	 */
	public function getSubscription() {
		if ($this->subscription === null) {
			$this->subscription = new PaidSubscription($this->subscriptionID);
		}
		
		return $this->subscription;
	}
	
	/**
	 * Returns the transaction log entry by transaction id or `null` if no such entry exists.
	 * 
	 * @param	integer		$paymentMethodObjectTypeID
	 * @param	string		$transactionID
	 * @return	PaidSubscriptionTransactionLog|null
	 */
	public static function getLogByTransactionID($paymentMethodObjectTypeID, $transactionID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_paid_subscription_transaction_log
			WHERE	paymentMethodObjectTypeID = ?
				AND transactionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$paymentMethodObjectTypeID, $transactionID]);
		$row = $statement->fetchArray();
		if ($row !== false) {
			return new PaidSubscriptionTransactionLog(null, $row);
		}
		
		return null;
	}
}
