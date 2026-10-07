<?php

namespace wcf\acp\form;

use wcf\command\user\CreateUser;
use wcf\command\user\UpdateUser;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\language\Language;
use wcf\data\style\Style;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\data\user\UserProfile;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\PasswordFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\UsernameFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\wysiwyg\WysiwygAttachmentFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\form\option\user\TUserOptionFormFields;
use wcf\system\language\LanguageFactory;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<User, UserBuilder>
 */
class UserAddForm extends AbstractDatabaseObjectBuilderForm
{
    use TUserOptionFormFields {
        getUserOptionFormField as getDefaultUserOptionFormField;
    }

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canAddUser'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = UserEditForm::class;

    /**
     * editable options grouped by the name of the top-level category and the
     * name of their category
     * @var array<string, array<string, UserOption[]>>
     */
    protected array $options = [];

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        foreach (['profile', 'settings'] as $categoryName) {
            $this->options[$categoryName] = $this->getUserOptionsByCategory(
                $categoryName,
                $this->isEditableOption(...)
            );
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserBuilder
    {
        if ($this->formObject !== null) {
            return UserBuilder::forUpdate($this->formObject);
        }

        return UserBuilder::forCreate()
            ->setRegistrationDate(\TIME_NOW);
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUser($builder);
        }

        return new CreateUser($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $tabMenu = TabMenuFormContainer::create('userTabs');
        $tabMenu->appendChild(
            TabFormContainer::create('generalTab')
                ->label('wcf.global.form.data')
                ->appendChild($this->getGeneralFormContainer())
        );

        foreach ($this->options as $categoryName => $options) {
            if ($options === []) {
                continue;
            }

            $tabMenu->appendChild(
                TabFormContainer::create($categoryName . 'Tab')
                    ->label('wcf.user.option.category.' . $categoryName)
                    ->appendChildren($this->getUserOptionFormContainers($options))
            );
        }

        if (\MODULE_USER_SIGNATURE !== 0) {
            $tabMenu->appendChild(
                TabFormContainer::create('signatureTab')
                    ->label('wcf.user.signature')
                    ->appendChild($this->getSignatureFormContainer())
            );
        }

        $this->form->appendChild($tabMenu);
    }

    /**
     * Returns the form container of the general tab.
     */
    protected function getGeneralFormContainer(): FormContainer
    {
        $container = FormContainer::create('general')
            ->appendChildren(\array_values(\array_filter([
                $this->getUsernameFormField(),
                $this->getEmailFormField(),
                $this->getPasswordFormField(),
                $this->getUserTitleFormField(),
                $this->getGroupsFormField(),
            ])));

        $availableLanguages = LanguageFactory::getInstance()->getLanguages();
        if (\count($availableLanguages) > 1) {
            $container->appendChildren($this->getLanguageFormFields($availableLanguages));
        }

        if ($this->formObject !== null) {
            $container->appendChildren($this->getStyleFormFields());
        }

        return $container;
    }

    protected function getUsernameFormField(): UsernameFormField
    {
        return UsernameFormField::create()
            ->required()
            ->autoFocus()
            ->exceptUser($this->formObject)
            ->saveValueCallback(function (UserBuilder $builder, UsernameFormField $field) {
                $username = $field->getSaveValue();
                $builder->setUsername($username);

                if (
                    $this->formObject !== null
                    && \mb_strtolower($username) !== \mb_strtolower($this->formObject->username)
                ) {
                    $builder->setOldUsername($this->formObject->username);
                    $builder->setLastUsernameChange(\TIME_NOW);
                }
            });
    }

    protected function getEmailFormField(): EmailFormField
    {
        return EmailFormField::create('email')
            ->label('wcf.user.email')
            ->required()
            ->available(
                $this->formObject === null
                    || WCF::getSession()->hasPermission('admin.user.canEditMailAddress')
            )
            ->addValidator(new FormFieldValidator('notUnique', function (EmailFormField $field) {
                $email = (string)$field->getValue();
                if (
                    $this->formObject !== null
                    && \mb_strtolower($email) === \mb_strtolower($this->formObject->email)
                ) {
                    return;
                }

                if (!User::getUserByEmail($email)->isGuest()) {
                    $field->addValidationError(new FormFieldValidationError(
                        'notUnique',
                        'wcf.user.email.error.notUnique'
                    ));
                }
            }))
            ->saveValueCallback(static function (UserBuilder $builder, EmailFormField $field) {
                $builder->setEmail($field->getSaveValue());
            });
    }

    protected function getPasswordFormField(): ?PasswordFormField
    {
        if (
            $this->formObject !== null
            && (
                !WCF::getSession()->hasPermission('admin.user.canEditPassword')
                || $this->formObject->authData !== ''
            )
        ) {
            return null;
        }

        $staticDictionary = [WCF::getUser()->username, WCF::getUser()->email];
        if ($this->formObject !== null) {
            $staticDictionary[] = $this->formObject->username;
            $staticDictionary[] = $this->formObject->email;
        }

        return PasswordFormField::create('password')
            ->label('wcf.user.password')
            ->required($this->formObject === null)
            ->autoComplete('new-password')
            ->passwordStrengthMeter()
            ->addMeterRelatedFieldId('username')
            ->addMeterRelatedFieldId('email')
            ->addStaticDictionary($staticDictionary)
            ->loadValueCallback(static function (User $user, PasswordFormField $field) {
                // The stored password hash must never be used as the value.
            })
            ->saveValueCallback(static function (UserBuilder $builder, PasswordFormField $field) {
                $password = (string)$field->getSaveValue();
                if ($password !== '') {
                    $builder->setPassword($password);
                }
            });
    }

    protected function getUserTitleFormField(): ?TextFormField
    {
        if (\MODULE_USER_RANK === 0) {
            return null;
        }

        return TextFormField::create('userTitle')
            ->label('wcf.user.userTitle')
            ->description('wcf.user.userTitle.description')
            ->maximumLength(\USER_TITLE_MAX_LENGTH)
            ->addValidator(new FormFieldValidator(
                'forbidden',
                static function (TextFormField $field) {
                    $userTitle = StringUtil::trim((string)$field->getValue());
                    if (!StringUtil::executeWordFilter($userTitle, \USER_FORBIDDEN_TITLES)) {
                        $field->addValidationError(new FormFieldValidationError(
                            'forbidden',
                            'wcf.user.userTitle.error.forbidden'
                        ));
                    }
                }
            ))
            ->saveValueCallback(static function (UserBuilder $builder, TextFormField $field) {
                $builder->setUserTitle(StringUtil::trim((string)$field->getValue()));
            });
    }

    protected function getGroupsFormField(): ?MultipleSelectionFormField
    {
        $availableGroups = UserGroup::getSortedAccessibleGroups(
            [],
            [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]
        );
        if ($availableGroups === []) {
            return null;
        }

        $ownerGroupID = UserGroup::getOwnerGroupID();
        $options = [];
        foreach ($availableGroups as $group) {
            $options[$group->groupID] = StringUtil::encodeHTML($group->getName());

            if ($group->groupID === $ownerGroupID) {
                $options[$group->groupID] .= ' ' . FontAwesomeIcon::fromValues('shield-halved')->toHtml(16);
            }
        }

        return MultipleSelectionFormField::create('groupIDs')
            ->label('wcf.acp.user.groups')
            ->options($options, false, false)
            ->ignoreInvalidValues()
            ->loadValueCallback(static function (User $user, MultipleSelectionFormField $field) {
                $field->value($user->getGroupIDs(true));
            })
            ->addValidator(new FormFieldValidator('ownerGroup', function (MultipleSelectionFormField $field) use ($ownerGroupID) {
                if (
                    $ownerGroupID !== null
                    && $this->formObject?->userID === WCF::getUser()->userID
                    && WCF::getUser()->hasOwnerAccess()
                    && !\in_array($ownerGroupID, \array_map(\intval(...), $field->getValue() ?? []), true)
                ) {
                    // Members of the owner group cannot remove themselves.
                    $field->addValidationError(new FormFieldValidationError(
                        'ownerGroup',
                        'wcf.acp.user.groups.error.ownerGroup'
                    ));
                }
            }))
            ->saveValueCallback(function (UserBuilder $builder, MultipleSelectionFormField $field) {
                $builder->setGroupIDs($this->getGroupIDsWithDefaultGroups(
                    \array_map(\intval(...), $field->getValue() ?? [])
                ));
            });
    }

    /**
     * Adds the default groups to the selected groups, which cannot be selected
     * explicitly.
     *
     * @param list<int> $groupIDs
     * @return list<int>
     */
    protected function getGroupIDsWithDefaultGroups(array $groupIDs): array
    {
        if ($this->formObject === null) {
            $defaultGroupIDs = UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]);
        } else {
            $defaultGroups = UserGroup::getAccessibleGroups([UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
            $defaultGroupIDs = \array_filter(
                $this->formObject->getGroupIDs(true),
                static fn(int $groupID) => isset($defaultGroups[$groupID])
            );
        }

        return \array_values(\array_unique(\array_merge($groupIDs, $defaultGroupIDs)));
    }

    /**
     * @param Language[] $availableLanguages
     * @return IFormField[]
     */
    protected function getLanguageFormFields(array $availableLanguages): array
    {
        $fields = [
            SelectFormField::create('languageID')
                ->label('wcf.user.language')
                ->description('wcf.user.language.description')
                ->options(\array_map(
                    static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                    $availableLanguages
                ))
                ->required()
                ->ignoreInvalidValues()
                ->defaultValue(LanguageFactory::getInstance()->getDefaultLanguageID())
                ->value(LanguageFactory::getInstance()->getDefaultLanguageID())
                ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                    $builder->setLanguageID((int)$field->getSaveValue());
                }),
        ];

        $availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
        if ($availableContentLanguages !== []) {
            $fields[] = MultipleSelectionFormField::create('visibleLanguages')
                ->label('wcf.user.visibleLanguages')
                ->description('wcf.user.visibleLanguages.description')
                ->options(\array_map(
                    static fn(Language $language) => StringUtil::encodeHTML($language->languageName),
                    $availableContentLanguages
                ))
                ->ignoreInvalidValues()
                ->loadValueCallback(static function (User $user, MultipleSelectionFormField $field) {
                    $field->value($user->getLanguageIDs());
                })
                ->saveValueCallback(
                    static function (UserBuilder $builder, MultipleSelectionFormField $field) use ($availableContentLanguages) {
                        $languageIDs = \array_map(\intval(...), $field->getValue() ?? []);
                        if ($languageIDs === []) {
                            $languageID = (int)$field->getDocument()->getFormField('languageID')?->getSaveValue();
                            if (isset($availableContentLanguages[$languageID])) {
                                $languageIDs[] = $languageID;
                            }
                        }

                        $builder->setLanguageIDs($languageIDs);
                    }
                );
        }

        return $fields;
    }

    /**
     * Returns the form fields for the style and the color scheme of an existing user.
     *
     * @return IFormField[]
     */
    protected function getStyleFormFields(): array
    {
        $fields = [];

        // Disabled styles are only available if the edited user may use them.
        $userProfile = new UserProfile($this->formObject);
        $availableStyles = \array_filter(
            StyleHandler::getInstance()->getStyles(),
            static fn(Style $style) => $style->isDisabled === 0
                || $userProfile->hasPermission('admin.style.canUseDisabledStyle')
        );
        if (\count($availableStyles) > 1) {
            $styles = [0 => 'wcf.global.defaultValue'];
            foreach ($availableStyles as $style) {
                $styles[$style->styleID] = StringUtil::encodeHTML($style->styleName);
            }

            $fields[] = SelectFormField::create('styleID')
                ->label('wcf.user.style')
                ->description('wcf.user.style.description')
                ->options($styles)
                ->required()
                ->ignoreInvalidValues()
                ->defaultValue(0)
                ->saveValueCallback(static function (UserBuilder $builder, SelectFormField $field) {
                    $builder->setStyleID((int)$field->getSaveValue());
                });
        }

        $fields[] = SelectFormField::create('colorScheme')
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
            });

        return $fields;
    }

    protected function getSignatureFormContainer(): WysiwygFormContainer
    {
        $container = WysiwygFormContainer::create('signature')
            ->label('wcf.user.signature')
            ->messageObjectType('com.woltlab.wcf.user.signature')
            ->attachmentData('com.woltlab.wcf.user.signature', objectID: $this->formObject->userID ?? 0);

        $container->getWysiwygField()
            ->loadValueCallback(static function (User $user, WysiwygFormField $field) {
                $field->value($user->signature ?? '');
            })
            ->saveValueCallback(static function (UserBuilder $builder, WysiwygFormField $field) {
                $builder
                    ->setSignatureHtmlInputProcessor($field->getHtmlInputProcessor())
                    ->setSignatureEnableHtml(true);
            });
        $container->getAttachmentField()->saveValueCallback(
            static function (UserBuilder $builder, WysiwygAttachmentFormField $field) {
                $attachmentHandler = $field->getAttachmentHandler();
                if ($attachmentHandler !== null) {
                    $builder->setSignatureAttachmentHandler($attachmentHandler);
                }
            }
        );

        return $container;
    }

    /**
     * Returns `true` if the active user can edit the given option of the
     * edited user.
     */
    protected function isEditableOption(UserOption $option): bool
    {
        if (!$option->validateOptions() || !$option->validatePermissions()) {
            return false;
        }

        return $option->isEditable(user: $this->formObject);
    }

    /**
     * Returns the form field for the given user option, administrators may
     * leave required options empty when editing other users.
     */
    protected function getUserOptionFormField(UserOption $option): AbstractFormField
    {
        $formField = $this->getDefaultUserOptionFormField($option);

        if ($this->formObject !== null && $this->formObject->userID !== WCF::getUser()->userID) {
            $formField->required(false);
        }

        return $formField;
    }
}
