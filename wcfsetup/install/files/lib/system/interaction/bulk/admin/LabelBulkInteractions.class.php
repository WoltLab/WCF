<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\label\LabelList;
use wcf\event\interaction\bulk\admin\LabelBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for labels.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class LabelBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.content.label.canManageLabel')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/labels/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new LabelBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return LabelList::class;
    }
}
