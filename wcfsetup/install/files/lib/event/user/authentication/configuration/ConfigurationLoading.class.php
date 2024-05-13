<?php

namespace wcf\event\user\authentication\configuration;

use wcf\event\IPsr14Event;
use wcf\system\user\authentication\configuration\UserAuthenticationConfiguration;

/**
 * Indicates the loading of the user auth configuration.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ConfigurationLoading implements IPsr14Event
{
    private UserAuthenticationConfiguration $configuration;

    public function register(UserAuthenticationConfiguration $configuration): void
    {
        if (isset($this->configuration)) {
            throw new \BadMethodCallException("A configuration has already been loaded");
        }

        $this->configuration = $configuration;
    }

    public function getConfigration(): ?UserAuthenticationConfiguration
    {
        return $this->configuration ?? null;
    }
}
