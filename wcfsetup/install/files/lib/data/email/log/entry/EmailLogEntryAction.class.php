<?php

namespace wcf\data\email\log\entry;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes email log entry-related actions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  EmailLogEntry        create()
 * @method  EmailLogEntryEditor[]    getObjects()
 * @method  EmailLogEntryEditor      getSingleObject()
 */
class EmailLogEntryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = EmailLogEntryEditor::class;

    /**
     * Deletes old log entries.
     */
    public function prune()
    {
        $sql = "SELECT  entryID
                FROM    wcf1_email_log_entry
                WHERE   time < ?";
        $statement = WCF::getDB()->prepare($sql, 65_000);
        $statement->execute([
            (\TIME_NOW - EmailLogEntry::LIFETIME),
        ]);
        $entryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        (new self($entryIDs, 'delete'))->executeAction();
    }
}
