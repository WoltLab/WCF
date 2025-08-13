<?php

namespace wcf\event\package\update\server;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\event\IPsr14Event;

/**
 * Indicates that a package update server has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PackageUpdateServerEnabled implements IPsr14Event
{
    public function __construct(public readonly PackageUpdateServer $server) {}
}
