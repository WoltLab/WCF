<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for the output of an url.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class URLUserOptionOutput implements IUserOptionOutput
{
    #[\Override]
    public function getOutput(User $user, UserOption $option, string $value)
    {
        if (empty($value) || $value === 'http://') {
            return '';
        }

        $value = self::getURL($value);

        return StringUtil::getAnchorTag($value, $value, true, true);
    }

    /**
     * Formats the URL.
     *
     * @return  string
     */
    private static function getURL(string $url)
    {
        if (!\preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }
}
