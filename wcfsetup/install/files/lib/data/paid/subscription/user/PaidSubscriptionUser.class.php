<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an association between a paid subscription and a user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\User
 * 
 * @property-read	integer		$subscriptionUserID		unique id of the paid subscription-user-association
 * @property-read	integer		$subscriptionID			id of the paid subscription the paid subscription-user-association belongs to
 * @property-read	integer		$userID				id of the user the paid subscription-user-association belongs to
 * @property-read	integer		$startDate			timestamp at which the paid subscription started
 * @property-read	integer		$endDate			timestamp at which the paid subscription ended or will end
 * @property-read	integer		$isActive			is `1` if the user's paid subscription is currently active and thus not expired, otherwise `0`
 * @property-read	integer		$sentExpirationNotification	is `1` if the user has been notified that the paid subscription is expiring
 */
class PaidSubscriptionUser extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'subscriptionUserID';
	
	/**
	 * paid subscription object
	 * @var	PaidSubscription
	 */
	protected $subscription = null;
	
	/**
	 * user object 
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Returns the paid subscription object.
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
	 * Sets the paid subscription object.
	 * 
	 * @param	PaidSubscription	$subscription
	 */
	public function setSubscription(PaidSubscription $subscription) {
		$this->subscription = $subscription;
	}
	
	/**
	 * Returns the user object.
	 * 
	 * @return      User
	 */
	public function getUser() {
		if ($this->user === null) {
			$this->user = new User($this->userID);
		}
		
		return $this->user;
	}
	
	/**
	 * Returns a specific subscription user or `null` if such a user does not exist.
	 * 
	 * @param	integer		$subscriptionID
	 * @param	integer		$userID
	 * @return	PaidSubscriptionUser|null
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
