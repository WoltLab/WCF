<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for the output of an url.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Option\User
 * @since       5.4
 */
class LabeledUrlUserOptionOutput implements IUserOptionOutput
{
    /**
     * @inheritDoc
     */
    public function getOutput(User $user, UserOption $option, $value)
    {
        if ($value) {
            return StringUtil::getAnchorTag(self::getURL($option, $value), $value, true, true);
        }

        return '';
    }

    /**
     * Formats the URL.
     */
    private static function getURL(UserOption $option, string $value): string
    {
        return \sprintf($option->labeledUrl, \rawurlencode($value));
    }
}
