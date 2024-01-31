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

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $options = Option::parseEnableOptions($option->enableOptions);

        WCF::getTPL()->assign([
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'option' => $option,
            'value' => $value,
        ]);

        return WCF::getTPL()->fetch('shared_booleanOptionType');
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if ($newValue == 1) {
            return 1;
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getSearchFormElement(Option $option, $value)
    {
        $options = Option::parseEnableOptions($option->enableOptions);

        return WCF::getTPL()->fetch('shared_booleanSearchableOptionType', 'wcf', [
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'option' => $option,
            'searchOption' => $this->forceSearchOption || ($value !== null && $value !== $option->defaultValue) || isset($_POST['searchOptions'][$option->optionName]),
            'value' => $value,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value)
    {
        if (!isset($_POST['searchOptions'][$option->optionName])) {
            return false;
        }

        $conditions->add("option_value.userOption" . $option->optionID . " = ?", [\intval($value)]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function addCondition(UserList $userList, Option $option, $value)
    {
        $userList->getConditionBuilder()->add(
            'user_option_value.userOption' . $option->optionID . ' = ?',
            [\intval($value)]
        );
    }

    /**
     * @inheritDoc
     */
    public function checkUser(User $user, Option $option, $value)
    {
        if (!$value) {
            return false;
        }

        return $user->getUserOption($option->optionName);
    }

    /**
     * @inheritDoc
     */
    public function getConditionData(Option $option, $newValue)
    {
        return $newValue;
    }

    /**
     * @inheritDoc
     */
    public function compare($value1, $value2)
    {
        if ($value1 == $value2) {
            return 0;
        }

        return $value1 ? 1 : -1;
    }

    /**
     * @inheritDoc
     */
    public function getDisabledOptionNames($value, $enableOptions)
    {
        $options = ArrayUtil::trim(\explode(',', $enableOptions));
        $result = [];

        foreach ($options as $item) {
            if ($item[0] == '!') {
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
