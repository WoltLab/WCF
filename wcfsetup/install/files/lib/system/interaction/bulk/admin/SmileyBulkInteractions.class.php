<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\smiley\SmileyList;
use wcf\event\interaction\bulk\admin\SmileyBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for smileys.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class SmileyBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_SMILEY === 0
            || !WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley')
        ) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/smilies/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new SmileyBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return SmileyList::class;
    }
}
