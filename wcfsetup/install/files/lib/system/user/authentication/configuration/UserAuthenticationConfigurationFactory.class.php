<?php

namespace wcf\system\user\authentication\configuration;

use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\system\user\authentication\configuration\event\ConfigurationLoading;

/**
 * Provides the instance of the active configuration of the user authentication process.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserAuthenticationConfigurationFactory extends SingletonFactory
{
    private UserAuthenticationConfiguration $configuration;

    #[\Override]
    protected function init()
    {
        $this->configuration = $this->getDefaultConfiguration();

        $event = new ConfigurationLoading();
        EventHandler::getInstance()->fire($event);
        if ($event->getConfigration()) {
            $this->configuration = $event->getConfigration();
        }
    }

    public function getConfigration(): UserAuthenticationConfiguration
    {
        return $this->configuration;
    }

    private function getDefaultConfiguration(): UserAuthenticationConfiguration
    {
        return new UserAuthenticationConfiguration(
            !\REGISTER_DISABLED,
        );
    }
}
