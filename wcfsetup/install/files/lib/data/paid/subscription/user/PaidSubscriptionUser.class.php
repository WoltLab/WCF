<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a paid subscription user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\User
 *
 * @property-read	integer		$subscriptionUserID
 * @property-read	integer		$subscriptionID
 * @property-read	integer		$userID
 * @property-read	integer		$startDate
 * @property-read	integer		$endDate
 * @property-read	integer		$isActive
 */
class PaidSubscriptionUser extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'paid_subscription_user';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'subscriptionUserID';
	
	/**
	 * paid subscription object
	 * @var	\wcf\data\paid\subscription\PaidSubscription
	 */
	protected $subscription = null;
	
	/**
	 * Gets the paid subscription object.
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
	 * Sets the paid subscription object.
	 * 
	 * @param	\wcf\data\paid\subscription\PaidSubscription		$subscription
	 */
	public function setSubscription(PaidSubscription $subscription) {
		$this->subscription = $subscription;
	}
	
	/**
	 * Gets a specific subscription user.
	 * 
	 * @param	integer		$subscriptionID
	 * @param	integer		$userID
	 * @return	\wcf\data\paid\subscription\user\PaidSubscriptionUser
	 */
	public static function getSubscriptionUser($subscriptionID, $userID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_paid_subscription_user
			WHERE	subscriptionID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$subscriptionID, $userID]);
		$row = $statement->fetchArray();
		if ($row !== false) {
			return new PaidSubscriptionUser(null, $row);
		}
		
		return null;
	}
}
