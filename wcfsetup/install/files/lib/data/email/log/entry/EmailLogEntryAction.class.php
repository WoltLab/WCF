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
 * @package WoltLabSuite\Core\Data\Email\Log\Entry
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
                FROM    wcf" . WCF_N . "_email_log_entry
                WHERE   time < ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            (\TIME_NOW - EmailLogEntry::LIFETIME),
        ]);
        $entryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        (new self($entryIDs, 'delete'))->executeAction();
    }
}
