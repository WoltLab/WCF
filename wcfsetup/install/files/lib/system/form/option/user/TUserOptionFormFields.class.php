<?php

namespace wcf\system\form\option\user;

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
use wcf\system\form\option\WysiwygFormOption;
use wcf\system\option\user\TUserOptionsByCategory;
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
    use TUserOptionsByCategory;

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
        if ($formOption instanceof WysiwygFormOption) {
            $formOption->setContext($option->messageObjectType ?: 'com.woltlab.wcf.user.aboutMe', 0);
        }
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
}
