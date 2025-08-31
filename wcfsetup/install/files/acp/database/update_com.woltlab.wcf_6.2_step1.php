<?php

/**
 * Updates the database layout during the update from 6.1 to 6.2.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_background_job')
        ->indices([
            DatabaseTableIndex::create('identifier')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['identifier']),
        ]),
    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            IntDatabaseTableColumn::create('avatarFileID')
                ->length(10)
                ->defaultValue(null),
            VarcharDatabaseTableColumn::create('avatarPathname')
                ->length(255)
                ->defaultValue(null),
            IntDatabaseTableColumn::create('coverPhotoFileID')
                ->length(10)
                ->defaultValue(null),
            IntDatabaseTableColumn::create('profileHits')
                ->drop(),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['avatarFileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL'),
            DatabaseTableForeignKey::create()
                ->columns(['coverPhotoFileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL'),
        ]),
    PartialDatabaseTable::create('wcf1_unfurl_url_image')
        ->columns([
            IntDatabaseTableColumn::create('fileID')
                ->length(10)
                ->defaultValue(null),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['fileID'])
                ->referencedTable('wcf1_file')
                ->referencedColumns(['fileID'])
                ->onDelete('SET NULL'),
        ]),
    PartialDatabaseTable::create('wcf1_contact_option')
        ->columns([
            MediumtextDatabaseTableColumn::create('configuration'),
        ]),
    PartialDatabaseTable::create('wcf1_file')
        ->columns([
            IntDatabaseTableColumn::create('uploadTime'),
            MediumtextDatabaseTableColumn::create('exifData'),
        ]),
    PartialDatabaseTable::create('wcf1_file_temporary')
        ->columns([
            MediumtextDatabaseTableColumn::create('exifData'),
        ]),
];
