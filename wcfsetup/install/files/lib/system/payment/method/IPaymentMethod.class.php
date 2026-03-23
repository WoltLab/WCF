<?php

namespace wcf\system\payment\method;

/**
 * Default interface for payment methods.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IPaymentMethod
{
    /**
     * Returns true, if this payment method supports recurring payments.
     *
     * @return  bool
     */
    public function supportsRecurringPayments();

    /**
     * Returns a list of supported currencies.
     *
     * @return  string[]
     */
    public function getSupportedCurrencies();

    /**
     * Returns the HTML code of the purchase button.
     *
     * @return  string
     */
    public function getPurchaseButton(
        float $cost,
        string $currency,
        string $name,
        string $token,
        string $returnURL,
        string $cancelReturnURL,
        bool $isRecurring = false,
        int $subscriptionLength = 0,
        string $subscriptionLengthUnit = ''
    );
}
