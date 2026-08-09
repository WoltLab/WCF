<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\ad\AdList;
use wcf\event\interaction\bulk\admin\AdBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for ads.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class AdBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_WCF_AD === 0
            || !WCF::getSession()->getPermission('admin.ad.canManageAd')
        ) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction("core/ads/%s")
        ]);

        EventHandler::getInstance()->fire(
            new AdBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return AdList::class;
    }
}
