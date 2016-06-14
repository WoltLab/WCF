<?php
namespace wcf\system\payment\method;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * IPaymentMethod implementation for Paypal.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
class PaypalPaymentMethod extends AbstractPaymentMethod {
	/**
	 * @inheritDoc
	 */
	public function supportsRecurringPayments() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSupportedCurrencies() {
		return [
			'AUD', // Australian Dollar
			'BRL', // Brazilian Real
			'CAD', // Canadian Dollar
			'CZK', // Czech Koruna
			'DKK', // Danish Krone
			'EUR', // Euro
			'HKD', // Hong Kong Dollar
			'HUF', // Hungarian Forint
			'ILS', // Israeli New Sheqel
			'JPY', // Japanese Yen
			'MYR', // Malaysian Ringgit
			'MXN', // Mexican Peso
			'NOK', // Norwegian Krone
			'NZD', // New Zealand Dollar
			'PHP', // Philippine Peso
			'PLN', // Polish Zloty
			'GBP', // Pound Sterling
			'RUB', // Russian Ruble
			'SGD', // Singapore Dollar
			'SEK', // Swedish Krona
			'CHF', // Swiss Franc
			'TWD', // Taiwan New Dollar
			'THB', // Thai Baht
			'TRY', // Turkish Lira
			'USD'  // U.S. Dollar
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '') {
		if ($isRecurring) {
			// subscribe button
			return '<form method="post" action="https://www.' . (ENABLE_DEBUG_MODE ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr">
					<input type="hidden" name="a3" value="'.$cost.'">
					<input type="hidden" name="p3" value="'.$subscriptionLength.'">
					<input type="hidden" name="t3" value="'.$subscriptionLengthUnit.'">
					<input type="hidden" name="src" value="1">
					<input type="hidden" name="business" value="'.StringUtil::encodeHTML(PAYPAL_EMAIL_ADDRESS).'">
					<input type="hidden" name="cancel_return" value="'.StringUtil::encodeHTML($cancelReturnURL).'">
					<input type="hidden" name="charset" value="utf-8">
					<input type="hidden" name="cmd" value="_xclick-subscriptions">
					<input type="hidden" name="currency_code" value="'.$currency.'">
					<input type="hidden" name="custom" value="'.StringUtil::encodeHTML($token).'">
					<input type="hidden" name="email" value="'.StringUtil::encodeHTML(WCF::getUser()->email).'">
					<input type="hidden" name="item_name" value="'.StringUtil::encodeHTML($name).'">
					<input type="hidden" name="lc" value="'.strtoupper(WCF::getLanguage()->languageCode).'">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="notify_url" value="'.StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('PaypalCallback', ['appendSession' => false])).'">
					<input type="hidden" name="quantity" value="1">
					<input type="hidden" name="return" value="'.StringUtil::encodeHTML($returnURL).'">
			
					<button class="small" type="submit">'.WCF::getLanguage()->get('wcf.payment.paypal.button.subscribe').'</button>
				</form>';
		}
		else {
			return '<form method="post" action="https://www.' . (ENABLE_DEBUG_MODE ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr">
					<input type="hidden" name="amount" value="'.$cost.'">
					<input type="hidden" name="business" value="'.StringUtil::encodeHTML(PAYPAL_EMAIL_ADDRESS).'">
					<input type="hidden" name="cancel_return" value="'.StringUtil::encodeHTML($cancelReturnURL).'">
					<input type="hidden" name="charset" value="utf-8">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="currency_code" value="'.$currency.'">
					<input type="hidden" name="custom" value="'.StringUtil::encodeHTML($token).'">
					<input type="hidden" name="email" value="'.StringUtil::encodeHTML(WCF::getUser()->email).'">
					<input type="hidden" name="item_name" value="'.StringUtil::encodeHTML($name).'">
					<input type="hidden" name="lc" value="'.strtoupper(WCF::getLanguage()->languageCode).'">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="notify_url" value="'.StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('PaypalCallback', ['appendSession' => false])).'">
					<input type="hidden" name="quantity" value="1">
					<input type="hidden" name="return" value="'.StringUtil::encodeHTML($returnURL).'">	
					
					<button class="small" type="submit">'.WCF::getLanguage()->get('wcf.payment.paypal.button.purchase').'</button>
				</form>';
		}
	}
}
