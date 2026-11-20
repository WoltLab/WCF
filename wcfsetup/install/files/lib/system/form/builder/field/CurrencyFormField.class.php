<?php

namespace wcf\system\form\builder\field;

use wcf\util\StringUtil;

/**
 * Implementation of a form field for currencies.
 *
 * The implementation is designed for two-decimal currencies like EUR or USD.
 * The API expect amounts to be provided in the smallest unit. For example, to set a value to 10 USD,
 * provide an amount value of 1000 (i.e., 1000 cents).
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class CurrencyFormField extends AbstractNumericFormField
{
    /**
     * @return 0|float
     */
    #[\Override]
    public function getSaveValue()
    {
        if ($this->getValue() === null && !$this->isNullable()) {
            return 0;
        }

        return \round($this->getValue() * 100);
    }

    #[\Override]
    public function value(mixed $value)
    {
        parent::value($value);

        if ($value !== null) {
            $this->value /= 100;
        }

        return $this;
    }

    /**
     * Sets the currency that is shown as the suffix of this field, e.g. `EUR`.
     * The currency is plain text and will be encoded.
     */
    public function currency(string $currency): static
    {
        $this->suffix = StringUtil::encodeHTML($currency);

        return $this;
    }
}
