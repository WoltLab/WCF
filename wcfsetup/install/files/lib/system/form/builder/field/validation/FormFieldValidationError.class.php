<?php

namespace wcf\system\form\builder\field\validation;

use wcf\system\WCF;

/**
 * Represents an error that occured during the validation of a form field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class FormFieldValidationError implements IFormFieldValidationError
{
    /**
     * additional error information, also used to resolve error message from language item
     * @var array<string, mixed>
     */
    private array $information;

    /**
     * language item containing the error message
     */
    private string $languageItem;

    /**
     * error type
     */
    private string $type;

    #[\Override]
    public function __construct(string $type, ?string $languageItem = null, array $information = [])
    {
        if ($languageItem === null) {
            $languageItem = 'wcf.global.form.error.' . $type;
        } elseif (!\is_string($languageItem)) {
            throw new \InvalidArgumentException(
                "Given language item is no string, '" . \gettype($languageItem) . "' given.'"
            );
        }

        $this->type = $type;
        $this->languageItem = $languageItem;
        $this->information = $information;
    }

    #[\Override]
    public function getHtml()
    {
        return WCF::getTPL()->render('wcf', 'shared_formFieldError', [
            'error' => $this,
        ]);
    }

    #[\Override]
    public function getInformation()
    {
        return $this->information;
    }

    #[\Override]
    public function getMessage()
    {
        return WCF::getLanguage()->getDynamicVariable($this->languageItem, $this->information);
    }

    #[\Override]
    public function getType()
    {
        return $this->type;
    }
}
