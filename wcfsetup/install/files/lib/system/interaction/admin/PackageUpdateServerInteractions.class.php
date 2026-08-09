<?php

namespace wcf\system\interaction\admin;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\event\interaction\admin\PackageUpdateServerInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for package update servers.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class PackageUpdateServerInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.configuration.package.canEditServer')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction(
                'core/packages/updates/servers/%s',
                static fn(PackageUpdateServer $server) => $server->canDelete()
            )
        ]);

        EventHandler::getInstance()->fire(
            new PackageUpdateServerInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return PackageUpdateServer::class;
    }
}
