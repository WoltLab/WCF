<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a paid subscription user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.user
 * @category	Community Framework
 */
class PaidSubscriptionUser extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'paid_subscription_user';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
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
		$statement->execute(array($subscriptionID, $userID));
		$row = $statement->fetchArray();
		if ($row !== false) {
			return new PaidSubscriptionUser(null, $row);
		}
		
		return null;
	}
}
