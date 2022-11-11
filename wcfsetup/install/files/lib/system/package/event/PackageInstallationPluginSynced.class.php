<?php

namespace wcf\system\package\event;

use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\system\event\IEvent;

/**
 * Indicates that the a package installation plugin
 * was executed through the developer tools.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Event
 * @since 6.0
 */
final class PackageInstallationPluginSynced implements IEvent
{
    public function __construct(
        public readonly PackageInstallationPlugin $pip,
        public readonly bool $isInvokedAgain,
    ) {
    }
}