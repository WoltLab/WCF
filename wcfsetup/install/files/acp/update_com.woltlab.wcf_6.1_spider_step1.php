<?php

/**
 * Drops old spider related columns and index from `wcf1_session` table.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */

use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

$tableNames = WCF::getDB()->getEditor()->getTableNames();
if (!\in_array('wcf1_spider', $tableNames)) {
    // The table `wcf1_spider` will be removed by a database PIP that is
    // executed after this script.
    return;
}

$tables = [
    PartialDatabaseTable::create('wcf1_session')
        ->columns([
            IntDatabaseTableColumn::create('spiderID')
                ->length(10)
                ->drop(),
        ])
        ->indices([
            DatabaseTableIndex::create('packageID')
                ->columns(['lastActivityTime', 'spiderID'])
                ->drop(),
        ])
        ->foreignKeys([
            // This foreign key definition fails to validate when the table
            // `wcf1_spider` no longer exists, despite it being scheduled for
            // removal.
            DatabaseTableForeignKey::create()
                ->columns(['spiderID'])
                ->referencedTable('wcf1_spider')
                ->referencedColumns(['spiderID'])
                ->onDelete('CASCADE')
                ->drop(),
        ]),
];

(new DatabaseTableChangeProcessor(
    /** @var ScriptPackageInstallationPlugin $this */
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
)
)->process();
