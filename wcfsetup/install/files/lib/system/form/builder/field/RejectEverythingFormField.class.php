<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * This form field always fails its validation.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class RejectEverythingFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    #[\Override]
    public function getFieldHtml(): string
    {
        return '';
    }

    #[\Override]
    public function getHtml(): string
    {
        return '';
    }

    #[\Override]
    public function readValue()
    {
        return $this;
    }

    #[\Override]
    public function validate()
    {
        $this->addValidationError(new FormFieldValidationError('rejectEverything'));
    }

    #[\Override]
    public function getSaveValue(): void
    {
        throw new \BadMethodCallException("Form field '{$this->getId()}' rejects everything.");
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'rejectEverything';
    }
}
