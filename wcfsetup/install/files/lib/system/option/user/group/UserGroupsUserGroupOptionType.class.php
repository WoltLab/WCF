<?php

namespace wcf\system\option\user\group;

use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * User group option type implementation for a user group select list.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupsUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType
{
    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        // get selected group
        $selectedGroups = \explode(',', $value ?? '');

        // get all groups
        $groups = UserGroup::getSortedGroupsByType();

        // generate html
        $html = '';
        foreach ($groups as $group) {
            $html .= '<label><input type="checkbox" name="values[' . StringUtil::encodeHTML($option->optionName) . '][]" value="' . $group->groupID . '"' . (\in_array(
                $group->groupID,
                $selectedGroups
            ) ? ' checked' : '') . '> ' . $group->getName() . '</label>';
        }

        return $html;
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        // get all groups
        $groups = UserGroup::getGroupsByType();

        // get new value
        if (!\is_array($newValue)) {
            $newValue = [];
        }
        $selectedGroups = ArrayUtil::toIntegerArray($newValue);

        // check groups
        foreach ($selectedGroups as $groupID) {
            if (!isset($groups[$groupID])) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }
        $newValue = ArrayUtil::toIntegerArray($newValue);
        \sort($newValue, \SORT_NUMERIC);

        return \implode(',', $newValue);
    }

    #[\Override]
    public function merge(mixed $defaultValue, mixed $groupValue)
    {
        $defaultValue = empty($defaultValue) ? [] : \explode(',', StringUtil::unifyNewlines($defaultValue));
        $groupValue = empty($groupValue) ? [] : \explode(',', StringUtil::unifyNewlines($groupValue));

        return \implode(',', \array_unique(\array_merge($defaultValue, $groupValue)));
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        $value1 = !empty($value1) ? \explode(',', $value1) : [];
        $value2 = !empty($value2) ? \explode(',', $value2) : [];

        // check if value1 contains more elements than value2
        $diff = \array_diff($value1, $value2);
        if ($diff !== []) {
            return 1;
        }

        // check if value1 contains less elements than value2
        $diff = \array_diff($value2, $value1);
        if ($diff !== []) {
            return -1;
        }

        // both lists are equal
        return 0;
    }

    #[\Override]
    public function getCSSClassName()
    {
        return 'checkboxList';
    }
}
