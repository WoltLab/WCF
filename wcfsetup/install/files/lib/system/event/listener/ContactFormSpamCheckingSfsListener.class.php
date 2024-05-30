<?php

namespace wcf\system\event\listener;

use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\event\page\ContactFormSpamChecking;

/**
 * Checks for spammers during the usage of the contact form using the data from Stop Forum Spam.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ContactFormSpamCheckingSfsListener
{
    public function __invoke(ContactFormSpamChecking $event): void
    {
        if (!\BLACKLIST_SFS_ENABLE) {
            return;
        }

        if (BlacklistEntry::getMatches('', $event->email, $event->ipAddress) !== []) {
            $event->preventDefault();
        }
    }
}
