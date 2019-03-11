<?php
namespace wcf\data\paid\subscription;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents a paid subscription.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription
 *
 * @property-read	integer		$subscriptionID			unique id of the paid subscription
 * @property-read	string		$title				title of the paid subscription or name of language item which contains the title
 * @property-read	string		$description			description of the paid subscription or name of language item which contains the description
 * @property-read	integer		$isDisabled			is `1` if the paid subscription is disabled and thus cannot be bought, otherwise `0`
 * @property-read	integer		$showOrder			position of the paid subscription in relation to the other paid subscriptions
 * @property-read	double		$cost				cost of the paid subscription
 * @property-read	string		$currency			identifier for the currency of the paid subscription cost
 * @property-read	integer		$subscriptionLength		magnitude part of the duration of the subscription or `0` if the subscription is permanent
 * @property-read	string		$subscriptionLengthUnit		unit part of the duration of the subscription (`D` for days, `M` for months, `Y` for years) or empty if the subscription is permanent
 * @property-read	integer		$isRecurring			is `1` if the paid subscription is recurring and thus requires regular (automatic) payments, otherwise `0`
 * @property-read	string		$groupIDs			comma-separated list with the ids of the user groups for which the subscription pays membership
 * @property-read	string		$excludedSubscriptionIDs	comma-separated list with the ids of paid subscriptions which prohibit purchase of this paid subscription
 */
class PaidSubscription extends DatabaseObject implements ITitledObject {
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
	
	/**
	 * Returns the formatted description, with support for legacy descriptions without HTML.
	 * 
	 * @return      string
	 */
	public function getFormattedDescription() {
		$description = $this->getDescription();
		if (preg_match('~^<[a-z]+~', $description)) {
			$processor = new HtmlOutputProcessor();
			$processor->process($description, 'com.woltlab.wcf.paidSubscription', $this->subscriptionID);
			
			return $processor->getHtml();
		}
		
		return nl2br($description, false);
	}
	
	/**
	 * Returns the description with transparent handling of phrases.
	 * 
	 * @return      string
	 */
	protected function getDescription() {
		if (preg_match('~^wcf.paidSubscription.subscription\d+.description$~', $this->description)) {
			return WCF::getLanguage()->get($this->description);
		}
		
		return $this->description;
	}
	
	/**
	 * @see		ITitledObject::getTitle()
	 * @since	3.1
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
}
