<?php

namespace wcf\system\package\event;

use wcf\system\event\IEvent;

/**
 * Indicates that the a package installation plugin
 * was executed through the developer tools.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated use `wcf\event\package\PackageInstallationPluginSynced` instead
 */
class PackageInstallationPluginSynced implements IEvent
{
    public function __construct(
        public readonly string $pluginName,
        public readonly bool $isInvokedAgain,
    ) {
    }
}
