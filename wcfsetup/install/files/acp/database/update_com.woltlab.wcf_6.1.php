<?php

/**
 * Updates the database layout during the update from 6.0 to 6.1.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar191DatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    DatabaseTable::create('wcf1_acp_dashboard_box_to_user')
        ->columns([
            NotNullVarchar191DatabaseTableColumn::create('boxName'),
            NotNullInt10DatabaseTableColumn::create('userID'),
            DefaultFalseBooleanDatabaseTableColumn::create('enabled'),
            NotNullInt10DatabaseTableColumn::create('showOrder')
                ->defaultValue(0),
        ])->indices([
            DatabaseTableIndex::create('boxToUser')
                ->columns(['boxName', 'userID'])
                ->type(DatabaseTableIndex::UNIQUE_TYPE),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
        ]),
    PartialDatabaseTable::create('wcf1_message_embedded_object')
        ->indices([
            DatabaseTableIndex::create('messageEmbeddedObject')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['messageObjectTypeID', 'messageID', 'embeddedObjectTypeID', 'embeddedObjectID']),
        ])
];
