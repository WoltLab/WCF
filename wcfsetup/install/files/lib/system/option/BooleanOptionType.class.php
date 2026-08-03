<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Option type implementation for boolean values.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BooleanOptionType extends AbstractOptionType implements ISearchableConditionUserOption
{
    /**
     * if `true`, the option is considered as being searched when generating the form element
     * @var bool
     */
    public $forceSearchOption = false;

    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        $options = Option::parseEnableOptions($option->enableOptions);

        return WCF::getTPL()->render('wcf', 'shared_booleanOptionType', [
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'option' => $option,
            'value' => $value,
        ]);
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        if ((bool)$newValue) {
            // @phpstan-ignore return.type
            return 1;
        }

        // @phpstan-ignore return.type
        return 0;
    }

    #[\Override]
    public function getSearchFormElement(Option $option, mixed $value)
    {
        $options = Option::parseEnableOptions($option->enableOptions);

        return WCF::getTPL()->render('wcf', 'shared_booleanSearchableOptionType', [
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'option' => $option,
            'searchOption' => $this->forceSearchOption || ($value !== null && $value !== $option->defaultValue) || isset($_POST['searchOptions'][$option->optionName]),
            'value' => $value,
        ]);
    }

    #[\Override]
    public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, mixed $value)
    {
        if (!isset($_POST['searchOptions'][$option->optionName])) {
            return false;
        }

        $conditions->add("option_value.userOption" . $option->optionID . " = ?", [\intval($value)]);

        return true;
    }

    #[\Override]
    public function addCondition(UserList $userList, Option $option, mixed $value)
    {
        $userList->getConditionBuilder()->add(
            'user_option_value.userOption' . $option->optionID . ' = ?',
            [\intval($value)]
        );
    }

    #[\Override]
    public function checkUser(User $user, Option $option, mixed $value)
    {
        if (!$value) {
            return false;
        }

        return $user->getUserOption($option->optionName);
    }

    #[\Override]
    public function getConditionData(Option $option, mixed $newValue)
    {
        return $newValue;
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        // @phpstan-ignore equal.notAllowed
        if ($value1 == $value2) {
            return 0;
        }

        return $value1 ? 1 : -1;
    }

    #[\Override]
    public function getDisabledOptionNames(mixed $value, string $enableOptions)
    {
        $options = ArrayUtil::trim(\explode(',', $enableOptions));
        $result = [];

        foreach ($options as $item) {
            if ($item[0] === '!') {
                if ($value) {
                    $result[] = $item;
                }
            } else {
                if (!$value) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }
}
