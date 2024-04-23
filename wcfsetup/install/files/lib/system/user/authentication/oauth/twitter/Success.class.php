<?php

namespace wcf\system\user\authentication\oauth\twitter;

use wcf\system\user\authentication\oauth\Success as BaseSuccess;

/**
 * Represents the request parameters for a successful OAuth 2 login to Twitter.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class Success extends BaseSuccess
{
    public function __construct(
        string $oauth_token,
        string $oauth_verifier,
    ) {
        parent::__construct($oauth_token, $oauth_verifier);
    }
}
