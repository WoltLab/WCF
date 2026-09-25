<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\notice\NoticeDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes a notice.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteNotice
{
    public function __construct(
        private readonly Notice $notice,
    ) {}

    public function __invoke(): void
    {
        NoticeBuilder::delete($this->notice);

        NoticeBuilder::resetCache();

        EventHandler::getInstance()->fire(new NoticeDeleted($this->notice));
    }
}
