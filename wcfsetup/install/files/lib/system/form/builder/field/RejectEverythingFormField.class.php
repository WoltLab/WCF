<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * This form field always fails its validation.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.4
 */
final class RejectEverythingFormField extends AbstractFormField
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    public function getFieldHtml(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHtml(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->addValidationError(new FormFieldValidationError('rejectEverything'));
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue(): void
    {
        throw new \BadMethodCallException('This form field rejects everything.');
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId(): string
    {
        return 'rejectEverything';
    }
}
