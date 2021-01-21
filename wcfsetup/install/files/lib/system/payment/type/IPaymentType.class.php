<?php

namespace wcf\system\payment\type;

/**
 * Default interface for payment types.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Payment\Type
 */
interface IPaymentType
{
    /**
     * Processes the given transaction.
     *
     * @param   int     $paymentMethodObjectTypeID
     * @param   string      $token
     * @param   float       $amount
     * @param   string      $currency
     * @param   string      $transactionID
     * @param   string      $status
     * @param   array       $transactionDetails
     */
    public function processTransaction($paymentMethodObjectTypeID, $token, $amount, $currency, $transactionID, $status, $transactionDetails);
}
