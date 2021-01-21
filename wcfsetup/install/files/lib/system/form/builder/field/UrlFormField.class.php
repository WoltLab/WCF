<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\Url;

/**
 * Implementation of a form field to enter a url.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
class UrlFormField extends TextFormField
{
    /**
     * @inheritDoc
     * @since       5.4
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return [
            'url',
            'photo',
            'impp',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getValidInputModes(): array
    {
        return ['url'];
    }

    /**
     * @inheritDoc
     */
    protected function validateText($text, ?Language $language = null)
    {
        if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '')) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        } elseif ($this->getValue() !== null && $this->getValue() !== '') {
            if (!Url::is($text)) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalid',
                    'wcf.form.field.url.error.invalid',
                    ['language' => $language]
                ));
            } else {
                parent::validateText($text, $language);
            }
        }
    }
}
