<?php

namespace wcf\system\user\authentication\configuration\event;

use wcf\system\event\IEvent;
use wcf\system\user\authentication\configuration\UserAuthenticationConfiguration;

/**
 * Indicates the loading of the configuration.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ConfigurationLoading implements IEvent
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
