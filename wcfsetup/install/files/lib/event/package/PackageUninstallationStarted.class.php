<?php

namespace wcf\event\package;

use wcf\data\package\Package;
use wcf\event\IPsr14Event;

/**
 * Indicates that a package uninstallation has been started but no data has been
 * removed so far.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PackageUninstallationStarted implements IPsr14Event
{
    public function __construct(
        public readonly Package $package
    ) {}
}
