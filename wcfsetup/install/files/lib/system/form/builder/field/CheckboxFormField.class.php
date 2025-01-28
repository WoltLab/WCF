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

    #[\Override]
    public function getSaveValue()
    {
        if ($this->isNullable() && !$this->value) {
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

        return WCF::getTPL()->fetch(
            'shared_checkboxFormField',
            'wcf',
            [
                'field' => $this,
            ]
        );
    }

    #[\Override]
    public function value($value)
    {
        if ($this->isNullable() && $value === null) {
            $value = 0;
        }

        return parent::value($value);
    }
}
