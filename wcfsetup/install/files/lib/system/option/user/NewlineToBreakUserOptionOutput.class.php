<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for a simple textarea value.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NewlineToBreakUserOptionOutput implements IUserOptionOutput
{
    /**
     * @inheritDoc
     */
    public function getOutput(User $user, UserOption $option, $value)
    {
        if ($value === null) {
            return '';
        }

        return \nl2br(StringUtil::encodeHTML($value), false);
    }
}
