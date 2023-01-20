<?php

namespace wcf\system\style\exception;

/**
 * Indicates that an unknown icon name was provided.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class UnknownIcon extends \Exception implements IconValidationFailed
{
    public function __construct(string $name, ?\Throwable $previous = null)
    {
        parent::__construct("The icon '{$name}' is unknown.", 0, $previous);
    }
}
