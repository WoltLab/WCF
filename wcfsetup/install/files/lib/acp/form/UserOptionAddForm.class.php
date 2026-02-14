<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Response\HtmlResponse;
use wcf\data\IStorableObject;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionAction;
use wcf\data\user\option\UserOptionEditor;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\error\HtmlErrorRenderer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\MultilineItemListFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\I18nHandler;
use wcf\system\option\user\DateUserOptionOutput;
use wcf\system\option\user\IUserOptionOutput;
use wcf\system\option\user\LabeledUrlUserOptionOutput;
use wcf\system\option\user\MessageUserOptionOutput;
use wcf\system\option\user\SelectOptionsUserOptionOutput;
use wcf\system\option\user\URLUserOptionOutput;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user option add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<UserOption>
 */
class UserOptionAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * available option categories
     * @var UserOptionCategory[]
     */
    public array $availableCategories = [];
    /**
     * available option types
     * @var string[]
     */
    public static $availableOptionTypes = [
        'birthday',
        'boolean',
        'checkboxes',
        'date',
        'integer',
        'float',
        'password',
        'multiSelect',
        'radioButton',
        'select',
        'text',
        'textarea',
        'message',
        'URL',
        'labeledUrl',
    ];

    /**
     * list of option type that require select options
     * @var string[]
     */
    public static $optionTypesUsingSelectOptions = [
        'checkboxes',
        'multiSelect',
        'radioButton',
        'select',
    ];

    /**
     * @inheritDoc
     */
    public $objectActionClass = UserOptionAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = UserOptionEditForm::class;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // get available categories
        $categoryList = new UserOptionCategoryList();
        $categoryList->getConditionBuilder()->add('parentCategoryName = ?', ['profile']);
        $categoryList->readObjects();
        $this->availableCategories = $categoryList->getObjects();

        if (empty($this->availableCategories)) {
            $this->setPsr7Response(
                new HtmlResponse(
                    (new HtmlErrorRenderer())->renderHtmlMessage(
                        WCF::getLanguage()->getDynamicVariable('wcf.global.error.title'),
                        WCF::getLanguage()->getDynamicVariable('wcf.acp.user.option.error.noCategories'),
                        null,
                        !WCF::getUser()->userID,
                    ),
                    403
                )
            );
        }
    }

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('optionName')
                        ->label('wcf.global.name')
                        ->required()
                        ->i18n()
                        ->i18nRequired()
                        ->languageItemPattern('wcf.user.option.(option\d+|\w+)'),
                    MultilineTextFormField::create('optionDescription')
                        ->label('wcf.acp.user.option.description')
                        ->i18n()
                        ->i18nRequired()
                        ->languageItemPattern('wcf.user.option.(option\d+|\w+).description'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.global.button.disable'),
                    SingleSelectionFormField::create('categoryName')
                        ->label('wcf.global.category')
                        ->required()
                        ->options(function () {
                            $options = [];
                            foreach ($this->availableCategories as $category) {
                                $options[$category->categoryName] = $category->getTitle();
                            }

                            return $options;
                        }),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.form.field.showOrder')
                        ->value(0)
                ]),
            FormContainer::create('typeDataContainer')
                ->label('wcf.acp.user.option.typeData')
                ->appendChildren([
                    SingleSelectionFormField::create('optionType')
                        ->label('wcf.acp.user.option.optionType')
                        ->description('wcf.acp.user.option.optionType.description')
                        ->required()
                        ->immutable($this->formAction !== 'create')
                        ->options(\array_combine(self::$availableOptionTypes, self::$availableOptionTypes))
                        ->value('text'),
                    TextFormField::create('defaultValue')
                        ->label('wcf.acp.user.option.defaultValue')
                        ->description('wcf.acp.user.option.defaultValue.description')
                        ->addFieldClass('long'),
                    MultilineItemListFormField::create('selectOptions')
                        ->label('wcf.acp.user.option.selectOptions')
                        ->description('wcf.acp.user.option.selectOptions.description')
                        ->required()
                        ->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_NSV)
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->fieldId('optionType')
                                ->values(self::$optionTypesUsingSelectOptions)
                        ),
                    TextFormField::create('labeledUrl')
                        ->label('wcf.acp.user.option.labeledUrl')
                        ->description('wcf.acp.user.option.labeledUrl.description')
                        ->addFieldClass('long')
                        ->required()
                        ->addValidator(
                            new FormFieldValidator('labeldUrlValidator', function (TextFormField $field) {
                                if (!\strpos($field->getValue(), '%s')) {
                                    $field->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.user.option.labeledUrl.error.invalid'
                                        )
                                    );
                                }
                            })
                        )
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->fieldId('optionType')
                                ->values(['labeledUrl'])
                        ),
                    ClassNameFormField::create('outputClass')
                        ->label('wcf.acp.user.option.outputClass')
                        ->description('wcf.acp.user.option.outputClass.description')
                        ->implementedInterface(IUserOptionOutput::class)
                ]),
            FormContainer::create('access')
                ->label('wcf.acp.user.option.access')
                ->appendChildren([
                    SingleSelectionFormField::create('editable')
                        ->label('wcf.acp.user.option.editable')
                        ->options([
                            1 => 'wcf.acp.user.option.editable.1',
                            2 => 'wcf.acp.user.option.editable.2',
                            3 => 'wcf.acp.user.option.editable.3',
                            6 => 'wcf.acp.user.option.editable.6',
                        ])
                        ->value(3),
                    SingleSelectionFormField::create('visible')
                        ->label('wcf.acp.user.option.visible')
                        ->options([
                            0 => 'wcf.acp.user.option.visible.0',
                            1 => 'wcf.acp.user.option.visible.1',
                            2 => 'wcf.acp.user.option.visible.2',
                            3 => 'wcf.acp.user.option.visible.3',
                            7 => 'wcf.acp.user.option.visible.7',
                            15 => 'wcf.acp.user.option.visible.15',
                        ])
                        ->value(15),
                    TextFormField::create('validationPattern')
                        ->label('wcf.acp.user.option.validationPattern')
                        ->description('wcf.acp.user.option.validationPattern.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('validationPatternOptionTypeDependency')
                                ->fieldId('optionType')
                                ->negate()
                                ->values(self::$optionTypesUsingSelectOptions)
                        ),
                    BooleanFormField::create('required')
                        ->label('wcf.acp.user.option.required')
                        ->value(false),
                    BooleanFormField::create('askDuringRegistration')
                        ->label('wcf.acp.user.option.askDuringRegistration')
                        ->value(false),
                    BooleanFormField::create('searchable')
                        ->label('wcf.acp.user.option.searchable')
                        ->value(false),
                ])
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'optionNameDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        // These values are unconditionally stored in phrases and
                        // never in actual columns as it is usually the case with
                        // the `I18nHandler`.
                        unset($parameters['data']['optionName']);
                        unset($parameters['data']['optionDescription']);

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof UserOption);
                        $data['optionName'] = 'wcf.user.option.' . $object->optionName;
                        $data['optionDescription'] = 'wcf.user.option.' . $object->optionName . '.description';

                        return $data;
                    }
                ),
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'additionDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $additionalData = $this->formObject?->additionalData ?: [];

                        if ($parameters['data']['optionType'] == 'select') {
                            $additionalData['allowEmptyValue'] = true;
                        } elseif ($parameters['data']['optionType'] == 'message') {
                            $additionalData['messageObjectType'] = 'com.woltlab.wcf.user.option.generic';
                        }

                        $parameters['data']['additionalData'] = \serialize($additionalData);

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'outputClassDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        if ($this->formAction !== 'create') {
                            return $parameters;
                        }

                        $outputClass = $parameters['data']['outputClass'];
                        $optionType = $parameters['data']['optionType'];

                        if (empty($outputClass)) {
                            if (\in_array($optionType, self::$optionTypesUsingSelectOptions)) {
                                $parameters['data']['outputClass'] = SelectOptionsUserOptionOutput::class;
                            } else {
                                $parameters['data']['outputClass'] = match ($optionType) {
                                    'date' => DateUserOptionOutput::class,
                                    'URL' => URLUserOptionOutput::class,
                                    'labeledUrl' => LabeledUrlUserOptionOutput::class,
                                    'message' => MessageUserOptionOutput::class,
                                    default => ''
                                };
                            }
                        }

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'defaultValueDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $optionType = $parameters['data']['optionType'];
                        $defaultValue = $parameters['data']['defaultValue'];

                        $parameters['data']['defaultValue'] = match ($optionType) {
                            'boolean', 'integer' => \intval($defaultValue),
                            'float' => \floatval($defaultValue),
                            'date' => \preg_match('/\d{4}-\d{2}-\d{2}/', $defaultValue) ? $defaultValue : '',
                            default => $defaultValue,
                        };

                        return $parameters;
                    }
                )
            );
    }

    #[\Override]
    public function save()
    {
        if ($this->formAction === 'create') {
            $this->additionalFields['optionName'] = StringUtil::getRandomID();
            $this->additionalFields['packageID'] = PACKAGE_ID;
        }

        parent::save();
    }

    #[\Override]
    public function saved()
    {
        $userOption = $this->objectAction->getReturnValues()['returnValues'];
        \assert($userOption instanceof UserOption);

        I18nHandler::getInstance()->save(
            'optionName',
            'wcf.user.option.option' . $userOption->optionID,
            'wcf.user.option'
        );
        I18nHandler::getInstance()->save(
            'optionDescription',
            'wcf.user.option.option' . $userOption->optionID . '.description',
            'wcf.user.option'
        );
        $editor = new UserOptionEditor($userOption);
        $editor->update([
            'optionName' => 'option' . $userOption->optionID,
        ]);

        parent::saved();
    }
}
