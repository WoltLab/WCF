<?php

namespace wcf\system\option\user\group;

use wcf\system\option\FileSizeOptionType;

/**
 * User group option type implementation for file size input fields.
 *
 * The merge of option values returns the highest value.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class FileSizeUserGroupOptionType extends FileSizeOptionType implements IUserGroupOptionType
{
    /**
     * @inheritDoc
     */
    public function merge($defaultValue, $groupValue)
    {
        if ($groupValue > $defaultValue) {
            return $groupValue;
        }
    }
}
