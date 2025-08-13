<?php

namespace wcf\command\notice;

use wcf\data\notice\Notice;
use wcf\event\notice\NoticeDismissed;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Dismisses a notice for the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DismissNotice
{
    public function __construct(
        private readonly Notice $notice,
    ) {}

    public function __invoke(): void
    {
        if (WCF::getUser()->userID) {
            $this->dismissForUser($this->notice, WCF::getUser()->userID);
        } else {
            $this->dismissForGuest($this->notice);
        }

        $event = new NoticeDismissed($this->notice);
        EventHandler::getInstance()->fire($event);
    }

    private function dismissForUser(Notice $notice, int $userID): void
    {
        $sql = "INSERT IGNORE INTO  wcf1_notice_dismissed
                                    (noticeID, userID)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $notice->noticeID,
            $userID,
        ]);

        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'dismissedNotices');
    }

    private function dismissForGuest(Notice $notice): void
    {
        $sessionVar = WCF::getSession()->getVar('dismissedNotices') ?? '';
        $dismissedNotices = @\unserialize($sessionVar) ?: [];
        $dismissedNotices[] = $notice->noticeID;

        WCF::getSession()->register('dismissedNotices', \serialize($dismissedNotices));
    }
}
