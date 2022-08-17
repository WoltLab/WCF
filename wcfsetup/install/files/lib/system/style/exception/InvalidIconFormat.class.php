<?php

namespace wcf\system\style\exception;

/**
 * Indicates that an invalid icon string was provided.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Style\Exception
 * @since 6.0
 */
final class InvalidIconFormat extends \Exception
{
    public function __construct()
    {
        parent::__construct('Expected a string containing an icon name and a boolean string separated by a null byte.');
    }
}
