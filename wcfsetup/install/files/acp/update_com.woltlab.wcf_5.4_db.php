<?php

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\BinaryDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarbinaryDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\package\plugin\ScriptPackageInstallationPlugin;
use wcf\system\WCF;

$tables = [
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
            DefaultFalseBooleanDatabaseTableColumn::create('multifactorActive'),
        ]),

    PartialDatabaseTable::create('wcf1_user_avatar')
        ->columns([
            DefaultFalseBooleanDatabaseTableColumn::create("hasWebP"),
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
];

(new DatabaseTableChangeProcessor(
    /** @var ScriptPackageInstallationPlugin $this */
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
))->process();
