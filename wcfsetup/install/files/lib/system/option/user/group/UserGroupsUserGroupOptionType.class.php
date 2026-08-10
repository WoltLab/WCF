<?php

namespace wcf\system\option\user\group;

use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\system\WCF;
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
    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        // get selected group
        $selectedGroups = \explode(',', $value ?? '');

        // get all groups
        $groups = UserGroup::getSortedGroupsByType();

        // generate html
        $html = '';
        foreach ($groups as $group) {
            if ($group->isOwner() && !WCF::getUser()->hasOwnerAccess()) {
                continue;
            }

            $html .= '<label><input type="checkbox" name="values[' . StringUtil::encodeHTML($option->optionName) . '][]" value="' . $group->groupID . '"' . (\in_array(
                $group->groupID,
                $selectedGroups
            ) ? ' checked' : '') . '> ' . StringUtil::encodeHTML($group->getName()) . '</label>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        // get all groups
        $groups = UserGroup::getGroupsByType();

        $selectedGroups = $this->getSelectedGroups($newValue);

        // check groups
        foreach ($selectedGroups as $groupID) {
            if (!isset($groups[$groupID])) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }

        // Only members of the owner group may reference it, otherwise an
        // administrator could grant themselves access to the owner group.
        $ownerGroupID = UserGroup::getOwnerGroupID();
        if (
            $ownerGroupID !== null
            && \in_array($ownerGroupID, $selectedGroups, true)
            && !WCF::getUser()->hasOwnerAccess()
        ) {
            throw new UserInputException($option->optionName, 'validationFailed');
        }
    }

    /**
     * Normalizes the submitted value, which is either the raw list of group ids
     * or the already serialized value returned by `getData()`.
     *
     * @return list<int>
     */
    private function getSelectedGroups(mixed $newValue): array
    {
        if (\is_string($newValue)) {
            $newValue = $newValue !== '' ? \explode(',', $newValue) : [];
        }
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        return ArrayUtil::toIntegerArray($newValue);
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }
        $newValue = ArrayUtil::toIntegerArray($newValue);
        \sort($newValue, \SORT_NUMERIC);

        return \implode(',', $newValue);
    }

    /**
     * @inheritDoc
     */
    public function merge($defaultValue, $groupValue)
    {
        $defaultValue = empty($defaultValue) ? [] : \explode(',', StringUtil::unifyNewlines($defaultValue));
        $groupValue = empty($groupValue) ? [] : \explode(',', StringUtil::unifyNewlines($groupValue));

        return \implode(',', \array_unique(\array_merge($defaultValue, $groupValue)));
    }

    /**
     * @inheritDoc
     */
    public function compare($value1, $value2)
    {
        $value1 = $value1 ? \explode(',', $value1) : [];
        $value2 = $value2 ? \explode(',', $value2) : [];

        // check if value1 contains more elements than value2
        $diff = \array_diff($value1, $value2);
        if (!empty($diff)) {
            return 1;
        }

        // check if value1 contains less elements than value2
        $diff = \array_diff($value2, $value1);
        if (!empty($diff)) {
            return -1;
        }

        // both lists are equal
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getCSSClassName()
    {
        return 'checkboxList';
    }
}
