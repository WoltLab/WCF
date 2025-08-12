<?php

namespace wcf\command\email\log\entry;

use wcf\data\email\log\entry\EmailLogEntry;
use wcf\system\WCF;

/**
 * Prunes old email log entries.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PruneEmailLogEntries
{
    public function __invoke(): void
    {
        $sql = "DELETE
                FROM   wcf1_email_log_entry
                WHERE  time < ?";
        $statement = WCF::getDB()->prepare($sql, 65_000);
        $statement->execute([
            (\TIME_NOW - EmailLogEntry::LIFETIME),
        ]);
    }
}
