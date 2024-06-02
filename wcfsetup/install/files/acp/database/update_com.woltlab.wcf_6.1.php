<?php

/**
 * Updates the database layout during the update from 6.0 to 6.1.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\CharDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar191DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarbinaryDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
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
        ]),
    PartialDatabaseTable::create('wcf1_background_job')
        ->columns([
            VarcharDatabaseTableColumn::create('identifier')
                ->length(191)
                ->defaultValue(null),
        ])
        ->indices([
            DatabaseTableIndex::create('identifier')
                ->columns(['identifier']),
        ]),
    DatabaseTable::create('wcf1_file')
        ->columns([
            ObjectIdDatabaseTableColumn::create('fileID'),
            NotNullVarchar255DatabaseTableColumn::create('filename'),
            BigintDatabaseTableColumn::create('fileSize')
                ->notNull(),
            CharDatabaseTableColumn::create('fileHash')
                ->length(64)
                ->notNull(),
            VarcharDatabaseTableColumn::create('fileExtension')
                ->length(10)
                ->notNull(),
            CharDatabaseTableColumn::create('secret')
                ->length(32)
                ->notNull(),
            IntDatabaseTableColumn::create('objectTypeID'),
            NotNullVarchar255DatabaseTableColumn::create('mimeType'),
            IntDatabaseTableColumn::create('width'),
            IntDatabaseTableColumn::create('height'),
            CharDatabaseTableColumn::create('fileHashWebp')
                ->length(64),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['fileID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('SET NULL'),
        ]),
    DatabaseTable::create('wcf1_file_temporary')
        ->columns([
            CharDatabaseTableColumn::create('identifier')
                ->length(40)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('time'),
            NotNullVarchar255DatabaseTableColumn::create('filename'),
            BigintDatabaseTableColumn::create('fileSize')
                ->notNull(),
            CharDatabaseTableColumn::create('fileHash')
                ->length(64)
                ->notNull(),
            VarcharDatabaseTableColumn::create('fileExtension')
                ->length(10)
                ->notNull(),
            CharDatabaseTableColumn::create('secret')
                ->length(32)
                ->notNull(),
            IntDatabaseTableColumn::create('objectTypeID'),
            TextDatabaseTableColumn::create('context'),
            VarbinaryDatabaseTableColumn::create('chunks')
                ->length(255)
                ->notNull(),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['identifier']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('SET NULL'),
        ]),
    DatabaseTable::create('wcf1_file_thumbnail')
        ->columns([
            ObjectIdDatabaseTableColumn::create('thumbnailID'),
            NotNullInt10DatabaseTableColumn::create('fileID'),
            VarcharDatabaseTableColumn::create('identifier')
                ->length(50)
                ->notNull(),
            CharDatabaseTableColumn::create('fileHash')
                ->length(64)
                ->notNull(),
            VarcharDatabaseTableColumn::create('fileExtension')
                ->length(10)
                ->notNull(),
            IntDatabaseTableColumn::create('width')
                ->notNull(),
            IntDatabaseTableColumn::create('height')
                ->notNull(),
            CharDatabaseTableColumn::create('formatChecksum')
                ->length(12),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['thumbnailID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['fileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('CASCADE'),
        ]),
];
