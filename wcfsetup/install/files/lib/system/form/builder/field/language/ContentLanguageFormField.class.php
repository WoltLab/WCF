<?php

namespace wcf\system\form\builder\field\language;

use wcf\data\language\Language;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\LanguageFactory;

/**
 * Implementation of a form field for to select the language of a certain content.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class ContentLanguageFormField extends AbstractFormField implements IImmutableFormField
{
    use TDefaultIdFormField;
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Language/ContentLanguage';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_contentLanguageFormField';

    public function __construct()
    {
        $this->label('wcf.user.language');
    }

    /**
     * @return Language[]
     */
    public function getContentLanguages(): array
    {
        return LanguageFactory::getInstance()->getContentLanguages();
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return LanguageFactory::getInstance()->multilingualismEnabled()
            && !empty(LanguageFactory::getInstance()->getContentLanguageIDs())
            && parent::isAvailable();
    }

    #[\Override]
    public function readValue(): static
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = \intval($this->getDocument()->getRequestData($this->getPrefixedId()));

            if (!$this->isRequired() && $this->value === 0) {
                $this->value = null;
            }
        }

        return $this;
    }

    #[\Override]
    public function validate(): void
    {
        if ($this->isRequired() && LanguageFactory::getInstance()->getLanguage($this->getValue()) === null) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'languageID';
    }
}
