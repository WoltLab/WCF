<?php

/**
 * Drops olds `wcf1_spider` table and create new columns to identify spiders in `wcf1_session` table.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */

use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_spider')
        ->drop(),
    PartialDatabaseTable::create('wcf1_session')
        ->columns([
            VarcharDatabaseTableColumn::create('spiderIdentifier')
                ->length(191)
                ->defaultValue(null),
        ])
        ->indices([
            DatabaseTableIndex::create('packageID')
                ->columns(['lastActivityTime', 'spiderIdentifier']),
        ]),
];
