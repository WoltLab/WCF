<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\notice\NoticeList;
use wcf\event\interaction\bulk\admin\NoticeBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class NoticeBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.notice.canManageNotice')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/notices/%s')
        ]);

        EventHandler::getInstance()->fire(
            new NoticeBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return NoticeList::class;
    }
}
