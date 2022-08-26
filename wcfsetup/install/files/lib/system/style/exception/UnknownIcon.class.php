<?php

namespace wcf\system\style\exception;

/**
 * Indicates that an unknown icon name was provided.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Style\Exception
 * @since 6.0
 */
final class UnknownIcon extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct("The icon '{$name}' is unknown.");
    }
}
