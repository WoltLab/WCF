<?php

namespace wcf\system\user\multifactor;

/**
 * Provides re-usable helper methods for use in multi-factor authentication.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\System\User\Multifactor
 * @since   5.4
 */
final class Helper
{
    /**
     * Generates a stream of digits.
     */
    public static function digitStream(): \Iterator
    {
        $i = 1;
        while (true) {
            yield $i++;
            if ($i > 9) {
                $i = 0;
            }
        }
    }
}
