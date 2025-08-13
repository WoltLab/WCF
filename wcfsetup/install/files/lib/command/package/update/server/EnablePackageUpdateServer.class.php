<?php

namespace wcf\command\package\update\server;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerEditor;
use wcf\event\package\update\server\PackageUpdateServerEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables the given package update server.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnablePackageUpdateServer
{
    public function __construct(private readonly PackageUpdateServer $server) {}

    public function __invoke(): void
    {
        (new PackageUpdateServerEditor($this->server))->update([
            'isDisabled' => 0,
        ]);

        $event = new PackageUpdateServerEnabled($this->server);
        EventHandler::getInstance()->fire($event);
    }
}
