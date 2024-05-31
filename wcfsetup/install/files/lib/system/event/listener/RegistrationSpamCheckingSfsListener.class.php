<?php

namespace wcf\system\event\listener;

use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\event\user\RegistrationSpamChecking;

/**
 * Checks for spammers during the registration using the data from Stop Forum Spam.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RegistrationSpamCheckingSfsListener
{
    public function __invoke(RegistrationSpamChecking $event): void
    {
        if (!\BLACKLIST_SFS_ENABLE) {
            return;
        }

        foreach (BlacklistEntry::getMatches($event->username, $event->email, $event->ipAddress) as $match) {
            $event->addMatch($match);
        }
    }
}
