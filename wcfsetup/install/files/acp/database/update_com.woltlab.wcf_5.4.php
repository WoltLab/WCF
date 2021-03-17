<?php

/**
 * Makes non-critical database adjustments (i.e. everything that is not related
 * to sessions).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\BinaryDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\EnumDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
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
    DatabaseTable::create('wcf1_email_log_entry')
        ->columns([
            ObjectIdDatabaseTableColumn::create('entryID'),
            NotNullInt10DatabaseTableColumn::create('time'),
            NotNullVarchar255DatabaseTableColumn::create('messageID'),
            NotNullVarchar255DatabaseTableColumn::create('subject'),
            NotNullVarchar255DatabaseTableColumn::create('recipient'),
            IntDatabaseTableColumn::create('recipientID')
                ->length(10)
                ->notNull(false),
            NotNullVarchar255DatabaseTableColumn::create('status'),
            TextDatabaseTableColumn::create('message'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['entryID']),
            DatabaseTableIndex::create('time')
                ->columns(['time']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['recipientID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
        ]),

    // This update script was added with 5.3.3. We need to ensure that the change is applied
    // when someone attempts to upgrade from an older 5.3.x for whatever reason.
    // If the database already has the proper state this will be a simple noop.
    //
    // see: https://github.com/WoltLab/WCF/commit/d836d365d30d44c6140dda17f82b9bd245db03e9
    PartialDatabaseTable::create('wcf1_event_listener')
        ->columns([
            EnumDatabaseTableColumn::create('environment')
                ->enumValues(['user', 'admin', 'all']),
        ]),

    DatabaseTable::create('wcf1_flood_control')
        ->columns([
            BigintDatabaseTableColumn::create('logID')
                ->length(20)
                ->notNull()
                ->autoIncrement(),
            NotNullInt10DatabaseTableColumn::create('objectTypeID'),
            BinaryDatabaseTableColumn::create('identifier')
                ->length(16)
                ->notNull(),
            NotNullInt10DatabaseTableColumn::create('time'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['logID']),
            DatabaseTableIndex::create()
                ->columns(['identifier']),
            DatabaseTableIndex::create()
                ->columns(['time']),
        ]),

    PartialDatabaseTable::create('wcf1_page_content')
        ->columns([
            TextDatabaseTableColumn::create('metaKeywords')
                ->drop(),
        ]),

    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('coverPhotoHasWebP'),
            DefaultFalseBooleanDatabaseTableColumn::create('multifactorActive'),
        ]),

    PartialDatabaseTable::create('wcf1_user_authentication_failure')
        ->columns([
            NotNullVarchar255DatabaseTableColumn::create('validationError')
                ->defaultValue(''),
        ]),

    PartialDatabaseTable::create('wcf1_user_avatar')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create("hasWebP"),
        ]),

    PartialDatabaseTable::create('wcf1_user_group')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('requireMultifactor'),
        ]),

    DatabaseTable::create('wcf1_user_multifactor')
        ->columns([
            ObjectIdDatabaseTableColumn::create('setupID'),
            NotNullInt10DatabaseTableColumn::create('userID'),
            NotNullInt10DatabaseTableColumn::create('objectTypeID'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['setupID']),
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['userID', 'objectTypeID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('wcf1_user_multifactor_backup')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('setupID'),
            NotNullVarchar255DatabaseTableColumn::create('identifier'),
            NotNullVarchar255DatabaseTableColumn::create('code'),
            NotNullInt10DatabaseTableColumn::create('createTime'),
            IntDatabaseTableColumn::create('useTime')
                ->length(10)
                ->defaultValue(null),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['setupID', 'identifier']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['setupID'])
                ->referencedTable('wcf1_user_multifactor')
                ->referencedColumns(['setupID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('wcf1_user_multifactor_email')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('setupID'),
            NotNullVarchar255DatabaseTableColumn::create('code'),
            NotNullInt10DatabaseTableColumn::create('createTime'),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['setupID', 'code']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['setupID'])
                ->referencedTable('wcf1_user_multifactor')
                ->referencedColumns(['setupID'])
                ->onDelete('CASCADE'),
        ]),

    DatabaseTable::create('wcf1_user_multifactor_totp')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('setupID'),
            NotNullVarchar255DatabaseTableColumn::create('deviceID'),
            NotNullVarchar255DatabaseTableColumn::create('deviceName'),
            VarbinaryDatabaseTableColumn::create('secret')
                ->notNull()
                ->length(255),
            NotNullInt10DatabaseTableColumn::create('minCounter'),
            NotNullInt10DatabaseTableColumn::create('createTime'),
            IntDatabaseTableColumn::create('useTime')
                ->length(10)
                ->notNull(false)
                ->defaultValue(null),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['setupID', 'deviceID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['setupID'])
                ->referencedTable('wcf1_user_multifactor')
                ->referencedColumns(['setupID'])
                ->onDelete('CASCADE'),
        ]),

    PartialDatabaseTable::create('wcf1_box')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('invertPermissions'),
        ]),

    PartialDatabaseTable::create('wcf1_page')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create('invertPermissions'),
        ]),

    DatabaseTable::create('wcf1_unfurl_url_image')
        ->columns([
            ObjectIdDatabaseTableColumn::create('imageID'),
            TextDatabaseTableColumn::create('imageUrl')
                ->notNull(),
            VarcharDatabaseTableColumn::create('imageUrlHash')
                ->notNull()
                ->length(40),
            NotNullInt10DatabaseTableColumn::create('width'),
            NotNullInt10DatabaseTableColumn::create('height'),
            VarcharDatabaseTableColumn::create('imageExtension')
                ->length(4),
            DefaultFalseBooleanDatabaseTableColumn::create('isStored'),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['imageID']),
            DatabaseTableIndex::create('imageUrlHash')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['imageUrlHash']),
        ]),

    DatabaseTable::create('wcf1_unfurl_url')
        ->columns([
            ObjectIdDatabaseTableColumn::create('urlID'),
            TextDatabaseTableColumn::create('url')
                ->notNull(),
            VarcharDatabaseTableColumn::create('urlHash')
                ->notNull()
                ->length(40),
            NotNullVarchar255DatabaseTableColumn::create('title')
                ->defaultValue(''),
            TextDatabaseTableColumn::create('description'),
            IntDatabaseTableColumn::create('imageID')
                ->length(10),
            NotNullVarchar255DatabaseTableColumn::create('status')
                ->defaultValue('PENDING'),
            NotNullInt10DatabaseTableColumn::create('lastFetch')
                ->defaultValue(0),
        ])
        ->indices([
            DatabaseTablePrimaryIndex::create()
                ->columns(['urlID']),
            DatabaseTableIndex::create('urlHash')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['urlHash']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['imageID'])
                ->referencedTable('wcf1_unfurl_url_image')
                ->referencedColumns(['imageID'])
                ->onDelete('SET NULL'),
        ]),
];
