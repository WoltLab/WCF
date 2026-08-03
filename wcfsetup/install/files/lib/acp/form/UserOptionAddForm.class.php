<?php

namespace wcf\acp\form;

use Laminas\Diactoros\Response\HtmlResponse;
use wcf\command\user\option\CreateUserOption;
use wcf\command\user\option\UpdateUserOption;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\http\error\HtmlErrorRenderer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\MultilineItemListFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\option\user\DateUserOptionOutput;
use wcf\system\option\user\IUserOptionOutput;
use wcf\system\option\user\LabeledUrlUserOptionOutput;
use wcf\system\option\user\MessageUserOptionOutput;
use wcf\system\option\user\SelectOptionsUserOptionOutput;
use wcf\system\option\user\URLUserOptionOutput;
use wcf\system\WCF;

/**
 * Shows the user option add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<UserOption, UserOptionBuilder>
 */
class UserOptionAddForm extends AbstractDatabaseObjectBuilderForm
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
    public string $objectEditLinkController = UserOptionEditForm::class;

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
                        WCF::getUser()->isGuest(),
                    ),
                    403
                )
            );
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserOptionBuilder
    {
        if ($this->formObject !== null) {
            return UserOptionBuilder::forUpdate($this->formObject);
        }

        return UserOptionBuilder::forCreate()->setGenericOptionName();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUserOption($builder);
        }

        return new CreateUserOption($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $formAction = $this->formAction;
        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    // The localized title and description are stored in the
                    // `wcf1_user_option_l10n` table via the builder, not in
                    // columns of `wcf1_user_option`.
                    TextFormField::create('optionName')
                        ->label('wcf.global.name')
                        ->required()
                        ->l10n()
                        ->saveValueCallback(static function (UserOptionBuilder $builder, TextFormField $field) {
                            $builder->setL10nTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->getL10nValues('title'));
                        }),
                    MultilineTextFormField::create('optionDescription')
                        ->label('wcf.acp.user.option.description')
                        ->l10n()
                        ->saveValueCallback(static function (UserOptionBuilder $builder, MultilineTextFormField $field) {
                            $builder->setL10nDescription($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->getL10nValues('description'));
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.global.button.disable')
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->isDisabled);
                        }),
                    SingleSelectionFormField::create('categoryName')
                        ->label('wcf.global.category')
                        ->required()
                        ->options(function () {
                            $options = [];
                            foreach ($this->availableCategories as $category) {
                                $options[$category->categoryName] = $category->getTitle();
                            }

                            return $options;
                        })
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setCategoryName((string)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->categoryName);
                        }),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.form.field.showOrder')
                        ->value(0)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setShowOrder((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->showOrder);
                        }),
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
                        ->value('text')
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setOptionType((string)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->optionType);
                        }),
                    TextFormField::create('defaultValue')
                        ->label('wcf.acp.user.option.defaultValue')
                        ->description('wcf.acp.user.option.defaultValue.description')
                        ->addFieldClass('long')
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->defaultValue ?? '');
                        })
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            // type-cast the default value
                            $defaultValue = $field->getValue();
                            $builder->setDefaultValue(
                                match ($field->getDocument()->getFormField('optionType')->getValue()) {
                                    'boolean', 'integer' => \intval($defaultValue),
                                    'float' => \floatval($defaultValue),
                                    'date' => \preg_match('/\d{4}-\d{2}-\d{2}/', (string)$defaultValue) ? $defaultValue : '',
                                    default => $defaultValue,
                                }
                            );
                        }),
                    MultilineItemListFormField::create('selectOptions')
                        ->label('wcf.acp.user.option.selectOptions')
                        ->description('wcf.acp.user.option.selectOptions.description')
                        ->required()
                        ->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_NSV)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setSelectOptions((string)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->selectOptions ?? '');
                        })
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
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setLabeledUrl((string)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->labeledUrl ?? '');
                        })
                        ->addDependency(
                            ValueFormFieldDependency::create('optionType')
                                ->fieldId('optionType')
                                ->values(['labeledUrl'])
                        ),
                    ClassNameFormField::create('outputClass')
                        ->label('wcf.acp.user.option.outputClass')
                        ->description('wcf.acp.user.option.outputClass.description')
                        ->implementedInterface(IUserOptionOutput::class)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) use ($formAction) {
                            // handle auto-assign of the output class on create
                            $outputClass = $field->getValue();
                            $optionType = $field->getDocument()->getFormField('optionType')->getValue();
                            if ($formAction === 'create' && $outputClass === '') {
                                if (\in_array($optionType, self::$optionTypesUsingSelectOptions)) {
                                    $outputClass = SelectOptionsUserOptionOutput::class;
                                } else {
                                    $outputClass = match ($optionType) {
                                        'date' => DateUserOptionOutput::class,
                                        'URL' => URLUserOptionOutput::class,
                                        'labeledUrl' => LabeledUrlUserOptionOutput::class,
                                        'message' => MessageUserOptionOutput::class,
                                        default => ''
                                    };
                                }
                            }
                            $builder->setOutputClass($outputClass);
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->outputClass);
                        }),
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
                        ->value(3)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setEditable((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->editable);
                        }),
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
                        ->value(15)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setVisible((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->visible);
                        }),
                    TextFormField::create('validationPattern')
                        ->label('wcf.acp.user.option.validationPattern')
                        ->description('wcf.acp.user.option.validationPattern.description')
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setValidationPattern((string)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->validationPattern ?? '');
                        })
                        ->addDependency(
                            ValueFormFieldDependency::create('validationPatternOptionTypeDependency')
                                ->fieldId('optionType')
                                ->negate()
                                ->values(self::$optionTypesUsingSelectOptions)
                        ),
                    BooleanFormField::create('required')
                        ->label('wcf.acp.user.option.required')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setRequired((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->required);
                        }),
                    BooleanFormField::create('askDuringRegistration')
                        ->label('wcf.acp.user.option.askDuringRegistration')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setAskDuringRegistration((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->askDuringRegistration);
                        }),
                    BooleanFormField::create('searchable')
                        ->label('wcf.acp.user.option.searchable')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setSearchable((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->searchable);
                        }),
                    BooleanFormField::create('showOnUserCard')
                        ->label('wcf.acp.user.option.showOnUserCard')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setShowOnUserCard((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (UserOption $object, IFormField $field) {
                            $field->value($object->showOnUserCard);
                        }),
                ]),
        ]);
    }

    #[\Override]
    public function save(): void
    {
        if ($this->formAction === 'create') {
            $this->additionalFields['packageID'] = \PACKAGE_ID;
        }

        $optionType = (string)$this->getFieldValue('optionType');

        // additionalData
        $additionalData = $this->formObject?->additionalData ?: [];
        if ($optionType === 'select') {
            $additionalData['allowEmptyValue'] = true;
        } elseif ($optionType === 'message') {
            $additionalData['messageObjectType'] = 'com.woltlab.wcf.user.option.generic';
        }
        $this->additionalFields['additionalData'] = \serialize($additionalData);

        parent::save();
    }

    /**
     * Returns the current value of the form field with the given id.
     */
    private function getFieldValue(string $id): mixed
    {
        $node = $this->form->getNodeById($id);
        \assert($node instanceof IFormField);

        return $node->getValue();
    }
}
