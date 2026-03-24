<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Option type implementation for radio buttons.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class RadioButtonOptionType extends AbstractOptionType implements
    ISearchableConditionUserOption,
    ISelectOptionOptionType
{
    /**
     * name of the template that contains the form element of this option type
     * @var string
     */
    public $templateName = 'radioButtonOptionType';

    /**
     * if `true`, the option is considered as being searched when generating the form element
     * @var bool
     */
    public $forceSearchOption = false;

    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        $availableOptions = $option->parseMultipleEnableOptions();
        $options = [
            'disableOptions' => [],
            'enableOptions' => [],
        ];

        foreach ($availableOptions as $key => $enableOptions) {
            $optionData = Option::parseEnableOptions($enableOptions);

            $options['disableOptions'][$key] = $optionData['disableOptions'];
            $options['enableOptions'][$key] = $optionData['enableOptions'];
        }

        // Check, if the current value is invalid and use a valid default value as current selection.
        if (!isset($this->getSelectOptions($option)[$value])) {
            $keys = \array_keys($this->getSelectOptions($option));
            $value = \reset($keys);
        }

        return WCF::getTPL()->render('wcf', $this->templateName, [
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'option' => $option,
            'selectOptions' => $this->getSelectOptions($option),
            'value' => $value,
        ]);
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        if (!empty($newValue)) {
            $options = $this->getSelectOptions($option);
            if (!isset($options[$newValue])) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }
    }

    #[\Override]
    public function getSearchFormElement(Option $option, mixed $value)
    {
        $this->templateName = 'shared_radioButtonSearchableOptionType';
        WCF::getTPL()->assign(
            'searchOption',
            $this->forceSearchOption || ($value !== null && $value !== $option->defaultValue) || isset($_POST['searchOptions'][$option->optionName])
        );

        return $this->getFormElement($option, $value);
    }

    #[\Override]
    public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, mixed $value)
    {
        if (!isset($_POST['searchOptions'][$option->optionName])) {
            return false;
        }

        $conditions->add("option_value.userOption" . $option->optionID . " = ?", [StringUtil::trim($value ?: '')]);

        return true;
    }

    #[\Override]
    public function addCondition(UserList $userList, Option $option, mixed $value)
    {
        $userList->getConditionBuilder()->add(
            'user_option_value.userOption' . $option->optionID . ' = ?',
            [StringUtil::trim($value)]
        );
    }

    #[\Override]
    public function checkUser(User $user, Option $option, mixed $value)
    {
        return \mb_strtolower($user->getUserOption($option->optionName)) == \mb_strtolower(StringUtil::trim($value));
    }

    #[\Override]
    public function getConditionData(Option $option, mixed $newValue)
    {
        return $newValue;
    }

    /**
     * Returns the select options for the given option.
     *
     * @param Option $option
     * @return  string[]
     */
    protected function getSelectOptions(Option $option)
    {
        return $option->parseSelectOptions();
    }

    #[\Override]
    public function getCSSClassName()
    {
        return 'checkboxList';
    }

    #[\Override]
    public function getDisabledOptionNames(mixed $value, string $enableOptions)
    {
        $valueToOptions = \explode("\n", StringUtil::trim(StringUtil::unifyNewlines($enableOptions)));

        $i = 0;
        foreach ($valueToOptions as $valueToOption) {
            if (\str_contains($valueToOption, ':')) {
                $optionData = \explode(':', $valueToOption);
                $key = \array_shift($optionData);
                $enableOptionValues = \implode(':', $optionData);
            } else {
                $key = $i;
                $enableOptionValues = $valueToOption;
            }

            if ($key == $value) {
                $options = ArrayUtil::trim(\explode(',', $enableOptionValues));
                $result = [];

                foreach ($options as $item) {
                    if ($item[0] == '!') {
                        $result[] = $item;
                    } else {
                        $result[] = $item;
                    }
                }

                return $result;
            }

            $i++;
        }

        return [];
    }
}
