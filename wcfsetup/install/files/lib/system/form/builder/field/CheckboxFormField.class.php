<?php

namespace wcf\system\form\builder\field;

use wcf\system\WCF;

/**
 * Implementation of a checkbox form field for boolean values.
 *
 * @author  Peter Lohse
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 */
class CheckboxFormField extends BooleanFormField implements INullableFormField
{
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/CheckedVoid';

    #[\Override]
    public function readValue()
    {
        $this->value = $this->getDocument()->hasRequestData($this->getPrefixedId());

        return $this;
    }

    /**
     * @return 0|1|null
     * @phpstan-ignore method.childReturnType
     */
    #[\Override]
    public function getSaveValue()
    {
        if ($this->isNullable() && empty($this->value)) {
            return null;
        }

        return parent::getSaveValue();
    }

    #[\Override]
    public function getHtml()
    {
        if ($this->requiresLabel() && $this->getLabel() === null) {
            throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
        }

        return WCF::getTPL()->render(
            'wcf',
            'shared_checkboxFormField',
            [
                'field' => $this,
            ]
        );
    }

    #[\Override]
    public function value(mixed $value)
    {
        if ($this->isNullable() && $value === null) {
            $value = 0;
        }

        return parent::value($value);
    }
}
