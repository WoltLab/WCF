<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\data\notice\NoticeBuilder;
use wcf\event\notice\NoticeEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a notice.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableNotice
{
    public function __construct(private readonly Notice $notice) {}

    public function __invoke(): void
    {
        NoticeBuilder::forUpdate($this->notice)
            ->setIsDisabled(false)
            ->update();

        NoticeBuilder::resetCache();

        $event = new NoticeEnabled($this->notice);
        EventHandler::getInstance()->fire($event);
    }
}
