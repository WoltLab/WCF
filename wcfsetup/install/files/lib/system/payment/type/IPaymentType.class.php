<?php

namespace wcf\system\payment\type;

/**
 * Default interface for payment types.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IPaymentType
{
    /**
     * Processes the given transaction.
     *
     * @param mixed[] $transactionDetails
     * @return void
     */
    public function processTransaction(
        int $paymentMethodObjectTypeID,
        string $token,
        float $amount,
        string $currency,
        string $transactionID,
        string $status,
        array $transactionDetails
    );
}
