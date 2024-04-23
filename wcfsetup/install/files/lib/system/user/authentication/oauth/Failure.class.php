<?php

namespace wcf\system\user\authentication\oauth;

/**
 * Represents request parameters for a failed/denied OAuth 2 login.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class Failure
{
    public function __construct(public readonly string $error)
    {
    }
}
