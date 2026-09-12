<?php

namespace wcf\data\email\log\entry;

use wcf\command\email\log\entry\PruneEmailLogEntries;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes email log entry-related actions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `EmailLogEntryBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<EmailLogEntry, EmailLogEntryEditor>
 */
class EmailLogEntryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = EmailLogEntryEditor::class;

    /**
     * Deletes old log entries.
     *
     * @return void
     * @deprecated 6.3 use the `PruneEmailLogEntries` command instead.
     */
    public function prune()
    {
        new PruneEmailLogEntries()();
    }
}
