<?php

namespace wcf\system\form\builder\field;

use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\Diactoros\Uri;
use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field to enter a url.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class UrlFormField extends TextFormField
{
    public function __construct()
    {
        parent::__construct();

        $this->inputMode('url');
    }

    /**
     * @inheritDoc
     * @return string[]
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
     * @return string[]
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
            try {
                $uri = new Uri($text);

                // `Uri` only accepts `http` and `https` but also permits relative
                // URLs, which browsers may interpret differently, for example
                // `java\tscript:` is treated as `javascript:`.
                if ($uri->getScheme() === '') {
                    throw new InvalidArgumentException();
                }
            } catch (InvalidArgumentException) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalid',
                    'wcf.form.field.url.error.invalid',
                    ['language' => $language]
                ));

                return;
            }

            parent::validateText($text, $language);
        }
    }

    /**
     * @inheritDoc
     */
    public function getInputType(): string
    {
        return 'url';
    }
}
