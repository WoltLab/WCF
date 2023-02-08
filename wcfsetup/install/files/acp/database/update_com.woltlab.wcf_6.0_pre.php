<?php

/**
 * Updates the database layout during the update from 5.5 to 6.0.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\VarbinaryDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_package_installation_node')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('nodeType'),
        ]),
    DatabaseTable::create('wcf1_package_audit_log')
        ->columns([
            BigintDatabaseTableColumn::create('logID')
                ->length(20)
                ->notNull()
                ->autoIncrement(),
            MediumtextDatabaseTableColumn::create('payload')
                ->notNull(),
            NotNullVarchar255DatabaseTableColumn::create('time'),
            NotNullVarchar255DatabaseTableColumn::create('wcfVersion'),
            NotNullVarchar255DatabaseTableColumn::create('requestId'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['logID']),
        ]),
    PartialDatabaseTable::create('wcf1_package_installation_file_log')
        ->columns([
            VarbinaryDatabaseTableColumn::create('sha256')
                ->length(32)
                ->defaultValue(null),
            BigintDatabaseTableColumn::create('lastUpdated')
                ->length(20)
                ->defaultValue(null),
        ]),
];
