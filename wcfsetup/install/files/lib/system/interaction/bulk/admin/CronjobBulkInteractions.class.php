<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobList;
use wcf\event\interaction\bulk\admin\CronjobBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkRpcInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for cronjobs.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CronjobBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.management.canManageCronjob')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/cronjobs/%s', static fn(Cronjob $cronjob) => $cronjob->isDeletable()),
            new BulkRpcInteraction('execute', 'core/cronjobs/%s/execute', 'wcf.acp.cronjob.execute')
        ]);

        EventHandler::getInstance()->fire(
            new CronjobBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return CronjobList::class;
    }
}
