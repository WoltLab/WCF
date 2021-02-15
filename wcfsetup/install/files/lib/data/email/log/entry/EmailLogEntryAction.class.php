<?php

namespace wcf\data\email\log\entry;

use wcf\data\AbstractDatabaseObjectAction;

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
}
