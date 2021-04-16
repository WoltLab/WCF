<?php

namespace wcf\system\option\user\group;

use wcf\system\option\LineBreakSeparatedTextOptionType;
use wcf\util\StringUtil;

/**
 * User group option type implementation for separate items that are stored as line break-separated
 * text.
 *
 * The merge of option values returns merge of all text values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Option\User\Group
 * @since   5.4
 */
class LineBreakSeparatedTextUserGroupOptionType extends LineBreakSeparatedTextOptionType implements IUserGroupOptionType
{
    /**
     * @inheritDoc
     */
    public function merge($defaultValue, $groupValue)
    {
        $defaultValue = empty($defaultValue) ? [] : \explode("\n", StringUtil::unifyNewlines($defaultValue));
        $groupValue = empty($groupValue) ? [] : \explode("\n", StringUtil::unifyNewlines($groupValue));

        return \implode("\n", \array_unique(\array_merge($defaultValue, $groupValue)));
    }
}
