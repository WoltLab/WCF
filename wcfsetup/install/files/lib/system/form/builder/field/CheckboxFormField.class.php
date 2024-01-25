<?php

namespace wcf\system\form\builder\field;

use wcf\system\template\SharedTemplateEngine;

/**
 * Implementation of a checkbox form field for boolean values.
 *
 * @author  Peter Lohse
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 */
class CheckboxFormField extends BooleanFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/CheckedVoid';

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        $this->value = $this->getDocument()->hasRequestData($this->getPrefixedId());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        if ($this->requiresLabel() && $this->getLabel() === null) {
            throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
        }

        return SharedTemplateEngine::getInstance()->fetch(
            'shared_checkboxFormField',
            'wcf',
            [
                'field' => $this,
            ]
        );
    }
}
