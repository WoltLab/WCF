<?php

namespace wcf\system\user\authentication\configuration;

/**
 * Represents the configuration of the user authentication process.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     WoltLab License <http://www.woltlab.com/license-agreement.html>
 */
final class UserAuthenticationConfiguration
{
    public function __construct(
        public readonly bool $canRegister = true,
        public readonly bool $canLogin = true,
        public readonly bool $canChangeUsername = true,
        public readonly bool $canChangeEmail = true,
        public readonly bool $canChangePassword = true,
    ) {
    }
}
