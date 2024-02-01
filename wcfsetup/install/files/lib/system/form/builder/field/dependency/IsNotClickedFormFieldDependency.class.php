<?php

namespace wcf\system\form\builder\field\dependency;

use wcf\system\exception\InvalidObjectArgument;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\IFormField;

/**
 * Represents a dependency that requires that a button has not been clicked.
 *
 * This dependency only works for `ButtonFormField` fields.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
class IsNotClickedFormFieldDependency extends AbstractFormFieldDependency
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_isNotClickedFormFieldDependency';

    /**
     * @inheritDoc
     */
    public function checkDependency()
    {
        $form = $this->getField()->getDocument();

        // If no request data is given, the button was not clicked.
        if (!$form->hasRequestData($this->getField()->getPrefixedId())) {
            return true;
        }

        // Otherwise, the button is clicked if the relevant request data entry contains
        // the button's value.
        return $form->getRequestData($this->getField()->getPrefixedId()) !== $this->getField()->getValue();
    }

    /**
     * @inheritDoc
     */
    public function field(IFormField $field)
    {
        if (!($field instanceof ButtonFormField)) {
            throw new InvalidObjectArgument($field, ButtonFormField::class, 'Field');
        }

        return parent::field($field);
    }
}
