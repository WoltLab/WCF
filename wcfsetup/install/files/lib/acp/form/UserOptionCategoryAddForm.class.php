<?php

namespace wcf\acp\form;

use wcf\command\user\option\category\CreateUserOptionCategory;
use wcf\command\user\option\category\UpdateUserOptionCategory;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryBuilder;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;

/**
 * Shows the form for adding new user option categories.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<UserOptionCategory, UserOptionCategoryBuilder>
 */
class UserOptionCategoryAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = UserOptionCategoryEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): UserOptionCategoryBuilder
    {
        if ($this->formObject !== null) {
            return UserOptionCategoryBuilder::forUpdate($this->formObject);
        }

        return UserOptionCategoryBuilder::forCreate()
            ->setPackageID(\PACKAGE_ID)
            ->setGenericCategoryName()
            ->setParentCategoryName('profile');
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateUserOptionCategory($builder);
        }

        return new CreateUserOptionCategory($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    // The localized title is stored in the
                    // `wcf1_user_option_category_l10n` table via the builder,
                    // not in columns of `wcf1_user_option_category`.
                    TextFormField::create('title')
                        ->label('wcf.global.name')
                        ->required()
                        ->l10n()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (UserOptionCategoryBuilder $builder, TextFormField $field): void {
                            $builder->setL10nTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (UserOptionCategory $object, IFormField $field): void {
                            $field->value($object->getL10nValues('title'));
                        }),
                    ShowOrderFormField::create()
                        ->options($this->getCategories(), labelLanguageItems: false)
                        ->saveValueCallback(static function (UserOptionCategoryBuilder $builder, ShowOrderFormField $field): void {
                            $showOrder = $field->getSaveValue();
                            if ($showOrder !== null) {
                                $builder->setShowOrder($showOrder);
                            }
                        })
                        ->loadValueCallback(static function (UserOptionCategory $object, IFormField $field): void {
                            $field->value($object->showOrder);
                        }),
                ]),
        ]);
    }

    /**
     * Returns the titles of the profile categories in the order of their
     * position, with array keys being the category ids.
     *
     * @return array<int, string>
     */
    protected function getCategories(): array
    {
        $categoryList = new UserOptionCategoryList();
        $categoryList->getConditionBuilder()->add('parentCategoryName = ?', ['profile']);
        $categoryList->sqlOrderBy = 'showOrder';
        $categoryList->readObjects();

        return \array_map(
            static fn($category) => $category->getTitle(),
            $categoryList->getObjects()
        );
    }
}
