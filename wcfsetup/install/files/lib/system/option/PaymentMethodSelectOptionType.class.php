<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\WCF;

/**
 * Option type implementation for selecting payment methods.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaymentMethodSelectOptionType extends AbstractOptionType
{
    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        $selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();

        return WCF::getTPL()->render('wcf', 'paymentMethodSelectOptionType', [
            'selectOptions' => $selectOptions,
            'option' => $option,
            'value' => \explode(',', $value),
        ]);
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        $selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();
        foreach ($newValue as $optionName) {
            if (!isset($selectOptions[$optionName])) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        if (!\is_array($newValue)) {
            return '';
        }

        return \implode(',', $newValue);
    }
}
