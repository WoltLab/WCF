<?php

namespace wcf\system\style\exception;

/**
 * Indicates that an unsupported icon size was requested.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Style\Exception
 * @since 6.0
 */
final class InvalidIconSize extends \OutOfBoundsException
{
    public function __construct(int $size)
    {
        parent::__construct("An invalid icon size of '{$size}' was provided.");
    }
}
