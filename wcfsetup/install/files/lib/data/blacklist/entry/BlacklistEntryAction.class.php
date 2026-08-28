<?php

namespace wcf\data\blacklist\entry;

use wcf\command\blacklist\ImportBlacklist;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes blacklist entry-related actions.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3
 *
 * @extends AbstractDatabaseObjectAction<BlacklistEntry, BlacklistEntryEditor>
 * @since 5.2
 */
class BlacklistEntryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BlacklistEntryEditor::class;

    /**
     * @return void
     * @deprecated 6.3 Use `ImportBlacklist` instead.
     */
    public function import()
    {
        (new ImportBlacklist())();
    }
}
