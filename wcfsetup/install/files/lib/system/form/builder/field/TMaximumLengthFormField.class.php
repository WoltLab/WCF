<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Provides default implementations of `IMaximumLengthFormField` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TMaximumLengthFormField
{
    /**
     * maximum length of the field value
     * @var ?int
     */
    protected $maximumLength;

    /**
     * Returns the maximum length of the values of this field or `null` if no placeholder
     * has been set.
     *
     * @return ?int
     */
    public function getMaximumLength()
    {
        return $this->maximumLength;
    }

    /**
     * Sets the maximum length of the values of this field. If `null` is passed, the
     * maximum length is removed.
     *
     * @return static this field
     *
     * @throws \InvalidArgumentException if the given maximum length is no integer or otherwise invalid
     */
    public function maximumLength(?int $maximumLength = null)
    {
        if ($maximumLength !== null) {
            if (!\is_int($maximumLength)) {
                throw new \InvalidArgumentException(
                    "Given maximum length is no int, '" . \gettype($maximumLength) . "' given for field '{$this->getId()}'."
                );
            }

            if ($maximumLength <= 0) {
                throw new \InvalidArgumentException(
                    "Maximum length must be positive, '{$maximumLength}' given for field '{$this->getId()}'."
                );
            }

            if ($this instanceof IMinimumLengthFormField) {
                $minimumLength = $this->getMinimumLength();
                if ($minimumLength !== null && $minimumLength > $maximumLength) {
                    throw new \InvalidArgumentException(
                        "Minimum length ({$minimumLength}) cannot be greater than maximum length ({$maximumLength}) for field '{$this->getId()}'."
                    );
                }
            }
        }

        $this->maximumLength = $maximumLength;

        return $this;
    }

    /**
     * Validates the maximum length of the given text.
     *
     * @return void
     */
    public function validateMaximumLength(
        string $text,
        ?Language $language = null,
        string $errorLanguageItem = 'wcf.form.field.text.error.maximumLength'
    ) {
        if ($this->getMaximumLength() !== null && \mb_strlen($text) > $this->getMaximumLength()) {
            $this->addValidationError(new FormFieldValidationError(
                'maximumLength',
                $errorLanguageItem,
                [
                    'language' => $language,
                    'length' => \mb_strlen($text),
                    'maximumLength' => $this->getMaximumLength(),
                ]
            ));
        }
    }
}
