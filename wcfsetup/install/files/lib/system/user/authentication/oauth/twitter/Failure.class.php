<?php

namespace wcf\system\user\authentication\oauth\twitter;

use wcf\system\user\authentication\oauth\Failure as BaseFailure;

/**
 * Represents request parameters for a failed/denied OAuth 2 login to Twitter.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Failure extends BaseFailure
{
    public function __construct(string $denied)
    {
        parent::__construct($denied);
    }
}
