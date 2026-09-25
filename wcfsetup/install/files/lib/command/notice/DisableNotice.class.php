<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\notice\NoticeDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a notice.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableNotice
{
    public function __construct(private readonly Notice $notice) {}

    public function __invoke(): void
    {
        NoticeBuilder::forUpdate($this->notice)
            ->setIsDisabled(true)
            ->update();

        NoticeBuilder::resetCache();

        $event = new NoticeDisabled($this->notice);
        EventHandler::getInstance()->fire($event);
    }
}
