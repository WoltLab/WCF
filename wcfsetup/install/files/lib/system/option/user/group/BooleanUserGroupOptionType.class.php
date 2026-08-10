<?php

namespace wcf\system\option\user\group;

use wcf\data\option\Option;
use wcf\system\option\BooleanOptionType;
use wcf\system\WCF;

/**
 * User group option type implementation for boolean values.
 *
 * The merge of option values returns true if at least one value is true.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType, IUserGroupGroupOptionType
{
    use TUserGroupOptionType;

    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        $options = Option::parseEnableOptions($option->enableOptions);

        return WCF::getTPL()->render('wcf', 'userGroupBooleanOptionType', [
            'disableOptions' => $options['disableOptions'],
            'enableOptions' => $options['enableOptions'],
            'group' => $this->userGroup,
            'option' => $option,
            'value' => $value,
        ]);
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        return ($newValue == -1) ? -1 : parent::getData($option, $newValue);
    }

    #[\Override]
    public function merge(mixed $defaultValue, mixed $groupValue)
    {
        // force value for 'Never'
        // @phpstan-ignore equal.notAllowed, equal.notAllowed (option values are untyped and can differ in type)
        if ($defaultValue == -1 || $groupValue == -1) {
            return -1;
        }

        // don't save if values are equal or $defaultValue is better
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($defaultValue == $groupValue || (!empty($defaultValue) && empty($groupValue))) {
            return;
        }

        return $groupValue;
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($value1 == $value2) {
            return 0;
            // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        } elseif ($value1 == -1) {
            // this is the `never` permission
            return -1;
        }

        return !empty($value1) ? 1 : -1;
    }
}
