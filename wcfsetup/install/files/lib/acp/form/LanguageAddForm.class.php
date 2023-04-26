<?php

namespace wcf\acp\form;

use wcf\data\language\Language;
use wcf\data\language\LanguageAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the language add form.
 *
 * @property    LanguageAction  $objectAction
 *
 * @author  Florian Gail, Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.language.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.language.canManageLanguage'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = LanguageAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = LanguageEditForm::class;

    /**
     * @var string[]
     */
    public array $locales;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $locales = \ResourceBundle::getLocales('');
        if ($locales === false) {
            throw new \RuntimeException('Unable to query the ICU database to retrieve the available locales.');
        }

        $displayLocale = WCF::getLanguage()->getLocale();
        foreach ($locales as $locale) {
            $this->locales[$locale] = \Locale::getDisplayName($locale, $displayLocale);
        }

        $collator = new \Collator($displayLocale);
        $collator->asort($this->locales, \Collator::SORT_STRING);
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $locales = [
            '' => WCF::getLanguage()->get('wcf.global.noSelection'),
            ...$this->locales,
        ];

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    TextFormField::create('languageName')
                        ->label('wcf.global.name')
                        ->description('wcf.acp.language.name.description')
                        ->maximumLength(255)
                        ->required(),
                    TextFormField::create('languageCode')
                        ->label('wcf.acp.language.code')
                        ->description('wcf.acp.language.code.description')
                        ->maximumLength(20)
                        ->required()
                        ->immutable($this->form->getFormMode() === IFormDocument::FORM_MODE_UPDATE)
                        ->addValidator(new FormFieldValidator('unique', function (TextFormField $formField) {
                            if ($formField->getValidationErrors() !== []) {
                                return;
                            }

                            if (
                                $this->formObject instanceof Language
                                && \mb_strtolower($this->formObject->languageCode) === $formField->getValue()
                            ) {
                                return;
                            }

                            if (LanguageFactory::getInstance()->getLanguageByCode($formField->getValue())) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.language.add.languageCode.error.notUnique'
                                ));
                            }
                        })),
                    TextFormField::create('countryCode')
                        ->label('wcf.acp.language.countryCode')
                        ->description('wcf.acp.language.countryCode.description')
                        ->maximumLength(10)
                        ->required(),
                    SingleSelectionFormField::create('locale')
                        ->label('wcf.acp.language.locale')
                        ->description('wcf.acp.language.locale.description')
                        ->options($locales)
                        ->addValidator(new FormFieldValidator(
                            'locale',
                            static function (SingleSelectionFormField $formField) {
                                if ($formField->getValue() === '') {
                                    return;
                                }

                                if ($formField->getValidationErrors() !== []) {
                                    return;
                                }

                                $languageCodeField = $formField->getDocument()->getNodeById('languageCode');
                                \assert($languageCodeField instanceof TextFormField);

                                [$languageCode] = \explode('_', $formField->getValue());
                                if ($languageCodeField->getValue() !== $languageCode) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'languageCodeMismatch',
                                            'wcf.acp.language.add.locale.error.languageCodeMismatch',
                                            [
                                                'locale' => $formField->getValue(),
                                            ]
                                        )
                                    );
                                }
                            }
                        )),
                    SingleSelectionFormField::create('sourceLanguageID')
                        ->label('wcf.acp.language.add.source')
                        ->description('wcf.acp.language.add.source.description')
                        ->options(LanguageFactory::getInstance()->getLanguages())
                        ->available($this->form->getFormMode() === IFormDocument::FORM_MODE_CREATE)
                        ->required(),
                ]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildForm()
    {
        parent::buildForm();

        $this->form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'lowercase',
            static function (IFormDocument $document, array $parameters) {
                $parameters['data']['languageCode'] = \mb_strtolower($parameters['data']['languageCode']);
                $parameters['data']['countryCode'] = \mb_strtolower($parameters['data']['countryCode']);

                return $parameters;
            }
        ));
        $this->form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'sourceLanguage',
            function (IFormDocument $document, array $parameters) {
                if ($document->getFormMode() !== IFormDocument::FORM_MODE_CREATE) {
                    return $parameters;
                }

                if (isset($parameters['data']['sourceLanguageID'])) {
                    $parameters['sourceLanguageID'] = $parameters['data']['sourceLanguageID'];
                    unset($parameters['data']['sourceLanguageID']);
                }

                return $parameters;
            }
        ));
    }
}
