<?php

namespace wcf\command\email\log\entry;

use wcf\data\email\log\entry\EmailLogEntry;
use wcf\data\email\log\entry\EmailLogEntryBuilder;

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
        EmailLogEntryBuilder::deleteCreatedBefore(\TIME_NOW - EmailLogEntry::LIFETIME);
    }
}
