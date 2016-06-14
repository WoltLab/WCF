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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\Transaction\Log
 *
 * @property-read	integer		$logID
 * @property-read	integer|null	$subscriptionUserID
 * @property-read	integer|null	$userID
 * @property-read	integer		$subscriptionID
 * @property-read	integer		$paymentMethodObjectTypeID
 * @property-read	integer		$logTime
 * @property-read	string		$transactionID
 * @property-read	string		$transactionDetails
 * @property-read	string		$logMessage
 */
class PaidSubscriptionTransactionLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'paid_subscription_transaction_log';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'logID';
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	protected $user = null;
	
	/**
	 * paid subscription object
	 * @var	\wcf\data\paid\subscription\PaidSubscription
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
	 * @return	\wcf\data\user\User
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
	 * @return	\wcf\data\paid\subscription\PaidSubscription
	 */
	public function getSubscription() {
		if ($this->subscription === null) {
			$this->subscription = new PaidSubscription($this->subscriptionID);
		}
		
		return $this->subscription;
	}
	
	/**
	 * Gets a transaction log entry by transaction id.
	 * 
	 * @param	integer		$paymentMethodObjectTypeID
	 * @param	string		$transactionID
	 * @return	\wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog
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
