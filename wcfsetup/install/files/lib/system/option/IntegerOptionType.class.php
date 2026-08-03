<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for integer input fields.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class IntegerOptionType extends TextOptionType
{
    /**
     * @inheritDoc
     */
    protected $inputClass = 'short';

    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        return WCF::getTPL()->render('wcf', 'integerOptionType', [
            'option' => $option,
            'inputClass' => $this->inputClass,
            'value' => $value,
        ]);
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        // @phpstan-ignore return.type
        return \intval($newValue);
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        // Safeguard against values outside of 32 bit integers.
        // Use the PHP constants once we have migrated to 64 bit only.
        if ($newValue < -2147483648) {
            throw new UserInputException($option->optionName, 'tooLow');
        }
        if ($newValue > 2147483647) {
            throw new UserInputException($option->optionName, 'tooHigh');
        }

        if ($option->minvalue !== null && $option->minvalue > $newValue) {
            throw new UserInputException($option->optionName, 'tooLow');
        }
        if ($option->maxvalue !== null && $option->maxvalue < $newValue) {
            throw new UserInputException($option->optionName, 'tooHigh');
        }
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($value1 == $value2) {
            return 0;
        }

        return ($value1 > $value2) ? 1 : -1;
    }
}
