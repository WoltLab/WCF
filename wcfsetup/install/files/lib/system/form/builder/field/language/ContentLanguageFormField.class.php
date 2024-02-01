<?php

namespace wcf\system\form\builder\field\language;

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

    /**
     * Creates a new instance of `ContentLanguageFormField`.
     */
    public function __construct()
    {
        $this->label('wcf.user.language');
    }

    /**
     * @inheritDoc
     */
    public function getContentLanguages()
    {
        return LanguageFactory::getInstance()->getContentLanguages();
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return LanguageFactory::getInstance()->multilingualismEnabled()
            && !empty(LanguageFactory::getInstance()->getContentLanguageIDs())
            && parent::isAvailable();
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = \intval($this->getDocument()->getRequestData($this->getPrefixedId()));

            if (!$this->isRequired() && !$this->value) {
                $this->value = null;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->isRequired() && LanguageFactory::getInstance()->getLanguage($this->getValue()) === null) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'languageID';
    }
}
