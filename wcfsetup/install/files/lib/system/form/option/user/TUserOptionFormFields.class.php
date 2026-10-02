<?php

namespace wcf\system\form\option\user;

use wcf\data\option\category\OptionCategory;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\data\user\UserBuilder;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\ICensorshipFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\WCF;

/**
 * Provides methods to edit user options with form builder forms that save
 * their values using a `UserBuilder`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TUserOptionFormFields
{
    /**
     * Returns the options of the child categories of the given category that
     * pass the given filter, grouped by the name of the child category. The
     * options of deeper categories are added to their child category.
     *
     * @param \Closure(UserOption): bool $filter
     * @return array<string, UserOption[]>
     */
    protected function getUserOptionsByCategory(string $parentCategoryName, \Closure $filter): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');

        $options = [];
        foreach ($categoryStructure[$parentCategoryName] ?? [] as $categoryName) {
            if (!$this->isAccessibleUserOptionCategory($categories[$categoryName])) {
                continue;
            }

            $categoryOptions = $this->getUserOptionsOfCategory($categoryName, $filter);
            if ($categoryOptions !== []) {
                $options[$categoryName] = $categoryOptions;
            }
        }

        return $options;
    }

    /**
     * Returns one form container per category with the form fields of the options.
     *
     * @param array<string, UserOption[]> $optionsByCategory
     * @return FormContainer[]
     */
    protected function getUserOptionFormContainers(array $optionsByCategory): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');

        $containers = [];
        foreach ($optionsByCategory as $categoryName => $options) {
            $containers[] = FormContainer::create('optionCategory' . $categories[$categoryName]->categoryID)
                ->label('wcf.user.option.category.' . $categoryName)
                ->appendChildren(\array_map($this->getUserOptionFormField(...), $options));
        }

        return $containers;
    }

    /**
     * Returns the form field for the given user option.
     */
    protected function getUserOptionFormField(UserOption $option): AbstractFormField
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

            // Legacy date options use `0000-00-00` to represent an empty value.
            if ($value !== null && $value !== '0000-00-00') {
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
     * Returns the options of the given category and its child categories that
     * pass the given filter.
     *
     * @param \Closure(UserOption): bool $filter
     * @return UserOption[]
     */
    private function getUserOptionsOfCategory(string $categoryName, \Closure $filter): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');
        $cachedOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
        $optionToCategories = UserOptionCacheBuilder::getInstance()->getData([], 'optionToCategories');

        $options = [];
        foreach ($optionToCategories[$categoryName] ?? [] as $optionName) {
            $option = $cachedOptions[$optionName];
            if ($filter($option)) {
                $options[] = $option;
            }
        }

        foreach ($categoryStructure[$categoryName] ?? [] as $childCategoryName) {
            if ($this->isAccessibleUserOptionCategory($categories[$childCategoryName])) {
                $options = \array_merge($options, $this->getUserOptionsOfCategory($childCategoryName, $filter));
            }
        }

        return $options;
    }

    private function isAccessibleUserOptionCategory(OptionCategory $category): bool
    {
        return $category->categoryName !== 'hidden'
            && $category->validateOptions()
            && $category->validatePermissions();
    }
}
