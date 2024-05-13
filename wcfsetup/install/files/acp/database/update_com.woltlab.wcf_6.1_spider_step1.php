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
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
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
            DatabaseTableForeignKey::create()
                ->columns(['spiderID'])
                ->referencedTable('wcf1_spider')
                ->referencedColumns(['spiderID'])
                ->onDelete('CASCADE')
                ->drop(),
        ]),
];
