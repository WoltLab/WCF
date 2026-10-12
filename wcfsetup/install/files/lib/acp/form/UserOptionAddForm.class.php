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
use wcf\http\error\HtmlErrorRenderer;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\WCF;

/**
 * Shows the user option add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormOptionBuilderForm<UserOption, UserOptionBuilder>
 */
class UserOptionAddForm extends AbstractFormOptionBuilderForm
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

        if ($this->availableCategories === []) {
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

        return UserOptionBuilder::forCreate()
            ->setPackageID(\PACKAGE_ID)
            ->setGenericOptionName();
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
    protected function applyOptionType(DatabaseObjectBuilder $builder, string $optionType, array $configuration): void
    {
        $builder
            ->setOptionType($optionType)
            ->setConfiguration($configuration);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

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
                        }),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.form.field.showOrder')
                        ->value(0)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setShowOrder($field->getSaveValue());
                        }),
                ]),
            FormContainer::create('typeDataContainer')
                ->label('wcf.acp.user.option.typeData')
                ->appendChildren([
                    $this->getOptionTypeFormField(),
                    ...$this->getSharedConfigurationFormFields(),
                    TextFormField::create('defaultValue')
                        ->label('wcf.acp.user.option.defaultValue')
                        ->description('wcf.acp.user.option.defaultValue.description')
                        ->addFieldClass('long')
                        ->addDependency(
                            // Text options provide their own default value in the configuration,
                            // social media profiles are specific to each user.
                            ValueFormFieldDependency::create('defaultValueOptionTypeDependency')
                                ->fieldId('optionType')
                                ->negate()
                                ->values(['text', 'socialMedia'])
                        )
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $defaultValue = (string)$field->getSaveValue();

                            $builder->setDefaultValue((string)match ($field->getDocument()->getFormField('optionType')->getSaveValue()) {
                                'boolean', 'integer' => (int)$defaultValue,
                                'float' => (float)$defaultValue,
                                'date' => \preg_match('/\d{4}-\d{2}-\d{2}/', $defaultValue) ? $defaultValue : '',
                                default => $defaultValue,
                            });
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
                        }),
                    TextFormField::create('validationPattern')
                        ->label('wcf.acp.user.option.validationPattern')
                        ->description('wcf.acp.user.option.validationPattern.description')
                        ->addDependency(
                            ValueFormFieldDependency::create('validationPatternOptionTypeDependency')
                                ->fieldId('optionType')
                                ->values(['text', 'textarea', 'url', 'email', 'password'])
                        )
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setValidationPattern((string)$field->getSaveValue());
                        }),
                    BooleanFormField::create('askDuringRegistration')
                        ->label('wcf.acp.user.option.askDuringRegistration')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setAskDuringRegistration((bool)$field->getSaveValue());
                        }),
                    BooleanFormField::create('searchable')
                        ->label('wcf.acp.user.option.searchable')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setSearchable((bool)$field->getSaveValue());
                        }),
                    BooleanFormField::create('showOnUserCard')
                        ->label('wcf.acp.user.option.showOnUserCard')
                        ->value(false)
                        ->saveValueCallback(static function (UserOptionBuilder $builder, IFormField $field) {
                            $builder->setShowOnUserCard((bool)$field->getSaveValue());
                        }),
                ])
        ]);
    }

    #[\Override]
    protected function getAvailableFormOptions(): array
    {
        return UserOption::getAvailableFormOptions();
    }

    #[\Override]
    protected function getSelectableFormOptions(): array
    {
        // The timezone is reserved for the built-in user setting.
        return \array_filter(
            $this->getAvailableFormOptions(),
            static fn($id) => $id !== 'timezone',
            \ARRAY_FILTER_USE_KEY,
        );
    }

    #[\Override]
    protected function getOptionTypeFormField(): SelectFormField
    {
        return parent::getOptionTypeFormField()
            ->value('text');
    }
}
