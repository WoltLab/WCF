<?php

namespace wcf\form;

use wcf\command\user\UpdateUser;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\language\Language;
use wcf\data\option\category\OptionCategory;
use wcf\data\style\Style;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\UserOption;
use wcf\data\user\trophy\UserTrophyList;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileAction;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\ICensorshipFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\language\LanguageFactory;
use wcf\system\menu\user\UserMenu;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the dynamic options edit form.
 *
 * @author      Marcel Werk, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<User, UserBuilder>
 */
final class SettingsForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    /**
     * option category
     */
    public string $category = 'general';

    /**
     * list of available content languages
     * @var Language[]
     */
    public array $availableContentLanguages = [];

    /**
     * list of available languages
     * @var Language[]
     */
    public array $availableLanguages = [];

    /**
     * list of available styles
     * @var Style[]
     */
    public array $availableStyles = [];

    /**
     * list of available trophies
     * @var Trophy[]
     */
    public array $availableTrophies = [];

    /**
     * editable options grouped by the name of their category
     * @var array<string, UserOption[]>
     */
    protected array $options = [];

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (!empty($_REQUEST['category'])) {
            $this->category = $_REQUEST['category'];

            // validate category
            if (UserOptionCategory::getCategoryByName('settings.' . $this->category) === null) {
                throw new IllegalLinkException();
            }
        }

        $this->formObject = new User(WCF::getUser()->userID);
        $this->options = $this->getAvailableOptions('settings.' . $this->category);

        if ($this->category === 'general') {
            $this->availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
            $this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
            $this->availableStyles = StyleHandler::getInstance()->getAvailableStyles();

            // read available trophies
            $trophyIDs = \array_unique(\array_map(static function ($userTrophy) {
                return $userTrophy->trophyID;
            }, UserTrophyList::getUserTrophies([WCF::getUser()->userID])[WCF::getUser()->userID]));

            $this->availableTrophies = TrophyCache::getInstance()->getTrophiesByID($trophyIDs);

            Trophy::sort($this->availableTrophies, 'showOrder');
        } elseif ($this->options === []) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserBuilder
    {
        return UserBuilder::forUpdate($this->formObject);
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        return new UpdateUser($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        if ($this->category === 'general') {
            $this->form->appendChildren($this->getGeneralFormContainers());
        }

        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        foreach ($this->options as $categoryName => $options) {
            $this->form->appendChild(
                FormContainer::create('optionCategory' . $categories[$categoryName]->categoryID)
                    ->label('wcf.user.option.category.' . $categoryName)
                    ->appendChildren(\array_map($this->getOptionFormField(...), $options))
            );
        }
    }

    /**
     * Returns the form containers for the static options of the general category.
     *
     * @return FormContainer[]
     */
    protected function getGeneralFormContainers(): array
    {
        $containers = [];

        if (\count($this->availableLanguages) > 1) {
            $languageContainer = FormContainer::create('languageSection')
                ->label('wcf.user.language')
                ->appendChild(
                    SelectFormField::create('languageID')
                        ->label('wcf.user.language.description')
                        ->options(\array_map(
                            static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                            $this->availableLanguages
                        ))
                        ->required()
                        ->ignoreInvalidValues()
                        ->defaultValue(LanguageFactory::getInstance()->getDefaultLanguageID())
                        ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                            $builder->setLanguageID((int)$field->getSaveValue());
                        })
                );

            if ($this->availableContentLanguages !== []) {
                $languageContainer->appendChild(
                    MultipleSelectionFormField::create('contentLanguageIDs')
                        ->label('wcf.user.visibleLanguages')
                        ->description('wcf.user.visibleLanguages.description')
                        ->options(\array_map(
                            static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                            $this->availableContentLanguages
                        ))
                        ->ignoreInvalidValues()
                        ->loadValueCallback(static function (User $user, MultipleSelectionFormField $field) {
                            $field->value($user->getLanguageIDs());
                        })
                        ->saveValueCallback(function (UserBuilder $builder, MultipleSelectionFormField $field) {
                            $languageIDs = \array_map(\intval(...), $field->getValue() ?? []);
                            if ($languageIDs === []) {
                                $languageID = (int)$field->getDocument()->getFormField('languageID')?->getSaveValue();
                                if (isset($this->availableContentLanguages[$languageID])) {
                                    $languageIDs[] = $languageID;
                                }
                            }

                            $builder->setLanguageIDs($languageIDs);
                        })
                );
            }

            $containers[] = $languageContainer;
        }

        $styleContainer = FormContainer::create('styleSection')
            ->label('wcf.user.styles');
        if (\count($this->availableStyles) > 1) {
            $styles = [0 => 'wcf.global.defaultValue'];
            foreach ($this->availableStyles as $style) {
                $styles[$style->styleID] = StringUtil::encodeHTML($style->styleName);
            }

            $styleContainer->appendChild(
                SelectFormField::create('styleID')
                    ->label('wcf.user.style')
                    ->description('wcf.user.style.description')
                    ->options($styles)
                    ->required()
                    ->ignoreInvalidValues()
                    ->defaultValue(0)
                    ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                        $builder->setStyleID((int)$field->getSaveValue());
                    })
            );
        }
        $styleContainer->appendChild(
            SelectFormField::create('colorScheme')
                ->label('wcf.user.style.colorScheme')
                ->description('wcf.user.style.colorScheme.description')
                ->options([
                    'system' => 'wcf.style.setColorScheme.system',
                    'light' => 'wcf.style.setColorScheme.light',
                    'dark' => 'wcf.style.setColorScheme.dark',
                ])
                ->required()
                ->ignoreInvalidValues()
                ->defaultValue('system')
                ->loadValueCallback(static function (User $user, SelectFormField $field) {
                    $field->value($user->getUserOption('colorScheme') ?? 'system');
                })
                ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                    $optionID = User::getUserOptionID('colorScheme');
                    if ($optionID !== null) {
                        $builder->setUserOption($optionID, (string)$field->getSaveValue());
                    }
                })
        );
        $containers[] = $styleContainer;

        $maxSpecialTrophies = (int)WCF::getSession()->getPermission('user.profile.trophy.maxUserSpecialTrophies');
        if (\MODULE_TROPHY !== 0 && $maxSpecialTrophies !== 0 && $this->availableTrophies !== []) {
            $trophies = [];
            foreach ($this->availableTrophies as $trophy) {
                $trophies[$trophy->trophyID] = $trophy->renderTrophy(32) . ' ' . StringUtil::encodeHTML($trophy->getTitle());
            }

            $containers[] = FormContainer::create('trophySection')
                ->label('wcf.user.trophy.trophies')
                ->appendChild(
                    MultipleSelectionFormField::create('specialTrophies')
                        ->label('wcf.user.trophy.specialTrophies')
                        ->description('wcf.user.trophy.specialTrophies.description')
                        ->options($trophies)
                        ->addValidator(new FormFieldValidator(
                            'maximumMultiples',
                            static function (MultipleSelectionFormField $formField) use ($maxSpecialTrophies) {
                                if (\count($formField->getValue() ?? []) > $maxSpecialTrophies) {
                                    $formField->addValidationError(new FormFieldValidationError(
                                        'tooMany',
                                        'wcf.user.trophy.specialTrophies.error.tooMany'
                                    ));
                                }
                            }
                        ))
                        ->ignoreInvalidValues()
                        ->loadValueCallback(static function (User $user, MultipleSelectionFormField $field) {
                            $field->value(\array_values(\array_unique(\array_map(
                                static fn(Trophy $trophy) => $trophy->trophyID,
                                (new UserProfile($user))->getSpecialTrophies()
                            ))));
                        })
                );
        }

        return $containers;
    }

    /**
     * Returns the form field for the given user option.
     */
    protected function getOptionFormField(UserOption $option): AbstractFormField
    {
        $formOption = $option->getFormOption();
        $configuration = $option->getConfiguration();

        $formField = $formOption->getFormField($option->getIdentifier(), $configuration)
            ->label('wcf.user.option.' . $option->optionName);

        $description = 'wcf.user.option.' . $option->optionName . '.description';
        if (WCF::getLanguage()->get($description, true) !== '') {
            $formField->description($description);
        }

        if ((bool)($configuration['required'] ?? false)) {
            $formField->required();
        }

        if ($formField instanceof ICensorshipFormField) {
            $formField->censorship();
        }

        if ($formField instanceof SelectFormField && isset($formField->getOptions()[$option->defaultValue ?? ''])) {
            $formField->defaultValue($option->defaultValue);
        }

        if (($option->validationPattern ?? '') !== '') {
            $formField->addValidator(new FormFieldValidator(
                'validationPattern',
                static function (IFormField $formField) use ($option) {
                    $value = (string)$formField->getValue();
                    if ($value !== '' && !\preg_match('~' . $option->validationPattern . '~', $value)) {
                        $formField->addValidationError(new FormFieldValidationError(
                            'validationFailed',
                            'wcf.user.option.error.validationFailed'
                        ));
                    }
                }
            ));
        }

        $formField->loadValueCallback(static function (User $user, IFormField $formField) use ($option, $formOption) {
            $value = $user->{$option->getIdentifier()};
            if ($value !== null) {
                $formField->value($formOption->unserializeValue((string)$value));
            }
        });
        $formField->saveValueCallback(static function (UserBuilder $builder, IFormField $formField) use ($option, $formOption) {
            $value = $formField->getSaveValue();

            $builder->setUserOption(
                $option->optionID,
                $value !== null ? $formOption->serializeValue($value) : $option->defaultValue
            );
        });

        return $formField;
    }

    /**
     * Returns the options of the child categories of the given category that
     * the active user can edit, grouped by the name of the child category.
     *
     * @return array<string, UserOption[]>
     */
    protected function getAvailableOptions(string $parentCategoryName): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');

        $options = [];
        foreach ($categoryStructure[$parentCategoryName] ?? [] as $categoryName) {
            if (!$this->isAccessibleCategory($categories[$categoryName])) {
                continue;
            }

            $categoryOptions = $this->getCategoryOptions($categoryName);
            if ($categoryOptions !== []) {
                $options[$categoryName] = $categoryOptions;
            }
        }

        return $options;
    }

    /**
     * Returns the editable options of the given category and its child categories.
     *
     * @return UserOption[]
     */
    protected function getCategoryOptions(string $categoryName): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');
        $cachedOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
        $optionToCategories = UserOptionCacheBuilder::getInstance()->getData([], 'optionToCategories');

        $options = [];
        foreach ($optionToCategories[$categoryName] ?? [] as $optionName) {
            $option = $cachedOptions[$optionName];
            if ($option->validateOptions() && $option->validatePermissions() && $option->isEditable()) {
                $options[] = $option;
            }
        }

        foreach ($categoryStructure[$categoryName] ?? [] as $childCategoryName) {
            if ($this->isAccessibleCategory($categories[$childCategoryName])) {
                $options = \array_merge($options, $this->getCategoryOptions($childCategoryName));
            }
        }

        return $options;
    }

    private function isAccessibleCategory(OptionCategory $category): bool
    {
        return $category->categoryName !== 'hidden'
            && $category->validateOptions()
            && $category->validatePermissions();
    }

    #[\Override]
    protected function setFormAction(): void
    {
        $parameters = [];
        if ($this->category !== 'general') {
            $parameters['category'] = $this->category;
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, $parameters));
    }

    #[\Override]
    protected function afterSave(): void
    {
        parent::afterSave();

        $specialTrophies = $this->form->getFormField('specialTrophies');
        if ($specialTrophies !== null) {
            $userProfileAction = new UserProfileAction([$this->object->userID], 'updateSpecialTrophies', [
                'trophyIDs' => \array_map(\intval(...), $specialTrophies->getValue() ?? []),
            ]);
            $userProfileAction->executeAction();
        }
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'category' => $this->category,
        ]);
    }

    #[\Override]
    public function show(): void
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.option.category.settings.' . $this->category);

        parent::show();
    }
}
