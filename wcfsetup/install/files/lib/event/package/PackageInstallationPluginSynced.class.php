<?php

namespace wcf\event\package;

use wcf\event\IPsr14Event;

/**
 * Indicates that the a package installation plugin was executed through the developer tools.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PackageInstallationPluginSynced extends \wcf\system\package\event\PackageInstallationPluginSynced implements IPsr14Event
{
    public function __construct(
        public readonly string $pluginName,
        public readonly bool $isInvokedAgain,
    ) {
    }
}
