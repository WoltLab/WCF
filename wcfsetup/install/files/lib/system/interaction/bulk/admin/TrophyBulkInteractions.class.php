<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\trophy\TrophyList;
use wcf\event\interaction\bulk\admin\TrophyBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class TrophyBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_TROPHY === 0
            || !WCF::getSession()->getPermission('admin.trophy.canManageTrophy')
        ) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/trophies/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new TrophyBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return TrophyList::class;
    }
}
