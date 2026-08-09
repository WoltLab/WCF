<?php

namespace wcf\system\interaction\admin;

use wcf\data\notice\Notice;
use wcf\event\interaction\admin\NoticeInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class NoticeInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.notice.canManageNotice')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction("core/notices/%s"),
        ]);

        EventHandler::getInstance()->fire(
            new NoticeInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Notice::class;
    }
}
