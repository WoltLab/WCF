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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription
 * @category	Community Framework
 */
class PaidSubscription extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'paid_subscription';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'subscriptionID';
	
	/**
	 * Returns list of purchase buttons.
	 * 
	 * @return	array<string>
	 */
	public function getPurchaseButtons() {
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.payment.type', 'com.woltlab.wcf.payment.type.paidSubscription');
		$buttons = array();
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
