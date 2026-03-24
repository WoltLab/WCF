<?php

namespace wcf\system\option\user\group;

use wcf\system\option\IntegerOptionType;

/**
 * User group option type implementation for integer input fields.
 *
 * The merge of option values returns the lowest value.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class InverseIntegerUserGroupOptionType extends IntegerOptionType implements IUserGroupOptionType
{
    #[\Override]
    public function merge(mixed $defaultValue, mixed $groupValue)
    {
        if ($defaultValue < $groupValue) {
            return;
        }

        return $groupValue;
    }

    #[\Override]
    public function compare(mixed $value1, mixed $value2)
    {
        if ($value1 == $value2) {
            return 0;
        }

        return ($value1 < $value2) ? 1 : -1;
    }
}
