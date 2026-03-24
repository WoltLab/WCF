<?php

namespace wcf\system\payment\method;

/**
 * Abstract implementation of a payment method.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractPaymentMethod implements IPaymentMethod
{
    #[\Override]
    public function supportsRecurringPayments()
    {
        return false;
    }
}
