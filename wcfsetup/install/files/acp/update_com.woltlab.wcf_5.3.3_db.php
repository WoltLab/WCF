<?php

use wcf\system\database\table\column\EnumDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

/**
 * Updates the database table layout from WoltLab Suite Core 5.3.2 to 5.3.3.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

$tables = [
    PartialDatabaseTable::create('wcf1_event_listener')
        ->columns([
            EnumDatabaseTableColumn::create('environment')
                ->enumValues(['user', 'admin', 'all'])
        ]),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor())
)->process();
