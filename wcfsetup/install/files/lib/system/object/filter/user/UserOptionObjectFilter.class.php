<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateRangeFormField;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\option\BooleanFormOption;
use wcf\system\form\option\IFormOption;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\option\user\TJoinUserOptionValues;
use wcf\system\WCF;

/**
 * Filters users by the value of a user option. The comparison depends on the
 * type of the option, e.g. text options match values that contain the filter
 * value. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, string>
 */
final class UserOptionObjectFilter implements IObjectListFilter
{
    use TJoinUserOptionValues;

    private readonly IFormOption $formOption;

    public function __construct(
        private readonly UserOption $option,
    ) {
        $this->formOption = $option->getFormOption();
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userOption.' . $this->option->optionName;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->option->getTitle();
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        $formField = $this->formOption
            ->getFilterFormField(
                'userOption_' . \str_replace('.', '_', $this->option->optionName),
                $this->option->getConfiguration()
            )
            ->label($this->getTitle());

        if ($formField instanceof DateRangeFormField || $formField instanceof NumericRangeFormField) {
            // At least one of the bounds is required.
            $formField->addValidator(new FormFieldValidator(
                'range',
                static function (DateRangeFormField|NumericRangeFormField $field) {
                    if ($field->getFromValue() === '' && $field->getToValue() === '') {
                        $field->addValidationError(new FormFieldValidationError('empty'));
                    }
                }
            ));
        } elseif (!($formField instanceof BooleanFormField)) {
            // A boolean field cannot be required, because "no" is a valid filter value.
            $formField->required();
        }

        return $formField;
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return $this->option->isDisabled === 0
            && $this->option->validateOptions()
            && $this->option->validatePermissions();
    }

    #[\Override]
    public function isRepeatable(): bool
    {
        return false;
    }

    /**
     * @param mixed $value the value of the form field, e.g. a boolean or a list of selected values
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        if (\is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $this->formOption->serializeValue($value);
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): string
    {
        return $serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): mixed
    {
        if ($this->formOption instanceof BooleanFormOption) {
            return $value === '1';
        }

        return $this->formOption->unserializeValue($value);
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.option.summary', [
            'title' => $this->getTitle(),
            'value' => $this->formOption->renderFilterValue($value, $this->option->getConfiguration()),
        ]);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, mixed $value): void
    {
        $this->joinUserOptionValues($list);

        $this->formOption->applyFilter($list, 'user_option_value.' . $this->option->getIdentifier(), $value);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        return $this->formOption->matchesFilterValue(
            (string)($object->getUserOption($this->option->optionName) ?? ''),
            $configuredValue,
            $this->option->getConfiguration()
        );
    }
}
