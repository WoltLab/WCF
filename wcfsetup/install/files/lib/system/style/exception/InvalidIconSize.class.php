<?php

namespace wcf\system\style\exception;

/**
 * Indicates that an unsupported icon size was requested.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class InvalidIconSize extends \OutOfBoundsException implements IconValidationFailed
{
    public function __construct(int $size, ?\Throwable $previous = null)
    {
        parent::__construct("An invalid icon size of '{$size}' was provided.", 0, $previous);
    }
}
