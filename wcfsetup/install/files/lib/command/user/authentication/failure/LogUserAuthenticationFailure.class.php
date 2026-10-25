<?php

namespace wcf\command\user\authentication\failure;

use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\user\authentication\failure\UserAuthenticationFailureBuilder;
use wcf\data\user\User;
use wcf\util\UserUtil;

/**
 * Logs a failed authentication attempt of the current request.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class LogUserAuthenticationFailure
{
    /**
     * @param 'user'|'admin' $environment
     */
    public function __construct(
        private readonly string $environment,
        private readonly ?User $user,
        private readonly string $username,
        private readonly string $validationError,
    ) {}

    public function __invoke(): UserAuthenticationFailure
    {
        return UserAuthenticationFailureBuilder::forCreate()
            ->setEnvironment($this->environment)
            ->setUserID($this->user !== null && !$this->user->isGuest() ? $this->user->userID : null)
            ->setUsername(\mb_substr($this->username, 0, 100))
            ->setTime(\TIME_NOW)
            ->setIpAddress(UserUtil::getIpAddress())
            ->setUserAgent(UserUtil::getUserAgent())
            ->setValidationError($this->validationError)
            ->create();
    }
}
