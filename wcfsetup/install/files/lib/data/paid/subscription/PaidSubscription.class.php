<?php
namespace wcf\data\paid\subscription;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a paid subscription.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription
 *
 * @property-read	integer		$subscriptionID
 * @property-read	string		$title
 * @property-read	string		$description
 * @property-read	integer		$isDisabled
 * @property-read	integer		$showOrder
 * @property-read	double		$cost
 * @property-read	string		$currency
 * @property-read	integer		$subscriptionLength
 * @property-read	string		$subscriptionLengthUnit
 * @property-read	integer		$isRecurring
 * @property-read	string		$groupIDs
 * @property-read	string		$excludedSubscriptionIDs
 */
class PaidSubscription extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'paid_subscription';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'subscriptionID';
	
	/**
	 * Returns list of purchase buttons.
	 * 
	 * @return	string[]
	 */
	public function getPurchaseButtons() {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.payment.type', 'com.woltlab.wcf.payment.type.paidSubscription');
		$buttons = [];
		foreach (PaymentMethodHandler::getInstance()->getPaymentMethods() as $paymentMethod) {
			// check if payment method supports recurring payments
			if ($this->isRecurring && !$paymentMethod->supportsRecurringPayments()) continue;
			
			// check supported currencies
			if (!in_array($this->currency, $paymentMethod->getSupportedCurrencies())) continue;
			
			$buttons[] = $paymentMethod->getPurchaseButton($this->cost, $this->currency, WCF::getLanguage()->get($this->title), $objectTypeID . ':' . WCF::getUser()->userID . ':' . $this->subscriptionID, LinkHandler::getInstance()->getLink('PaidSubscriptionReturn'), LinkHandler::getInstance()->getLink(), $this->isRecurring, $this->subscriptionLength, $this->subscriptionLengthUnit);
		}
		
		return $buttons;
	}
	
	/**
	 * Returns a DateInterval object based on subscription length.
	 * 
	 * @return	\DateInterval
	 */
	public function getDateInterval() {
		return new \DateInterval('P' . $this->subscriptionLength . $this->subscriptionLengthUnit);
	}
}
