<?php

namespace wcf\system\option\user\group;

/**
 * User group option type implementation for integer input fields with an option
 * for an infinite value.
 *
 * The merge of option values returns true if at least one value is -1. Otherwise
 * it returns the highest value.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class InfiniteIntegerUserGroupOptionType extends IntegerUserGroupOptionType
{
    #[\Override]
    public function merge(mixed $defaultValue, mixed $groupValue)
    {
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($defaultValue == -1) {
            return null;
            // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        } elseif ($groupValue == -1) {
            return $groupValue;
        } else {
            return parent::merge($defaultValue, $groupValue);
        }
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($value1 == $value2) {
            return 0;
        }

        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($value1 == -1) {
            return 1;
            // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        } elseif ($value2 == -1) {
            return -1;
        }

        return parent::compare($value1, $value2);
    }
}
