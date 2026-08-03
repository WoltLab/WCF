<?php

namespace wcf\system\option\user\group;

/**
 * User group option type implementation for integer input fields.
 *
 * The merge of option values returns -1 if all values are -1 otherwise the lowest
 * value.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class InfiniteInverseIntegerUserGroupOptionType extends InverseIntegerUserGroupOptionType
{
    #[\Override]
    public function merge(mixed $defaultValue, mixed $groupValue)
    {
        // @phpstan-ignore equal.notAllowed, equal.notAllowed (option values are untyped and can differ in type)
        if ($groupValue == -1 || $defaultValue == $groupValue) {
            return;
        }

        // @phpstan-ignore equal.notAllowed (option values are untyped and can differ in type)
        if ($defaultValue == -1) {
            return $groupValue;
        }

        return \min($defaultValue, $groupValue);
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

        return ($value1 < $value2) ? 1 : -1;
    }
}
