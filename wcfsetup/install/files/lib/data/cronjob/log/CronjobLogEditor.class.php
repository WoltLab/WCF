<?php

namespace wcf\data\cronjob\log;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit cronjob logs.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `CronjobLogBuilder` instead.
 *
 * @mixin       CronjobLog
 * @extends DatabaseObjectEditor<CronjobLog>
 */
class CronjobLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CronjobLog::class;

    /**
     * Deletes all cronjob logs.
     *
     * @return void
     * @deprecated  6.3 Use `CronjobLogBuilder::clearAll()` instead.
     */
    public static function clearLogs()
    {
        // delete logs
        $sql = "DELETE FROM wcf1_cronjob_log";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }
}
