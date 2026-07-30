<?php

/**
 * Adds the `l10nIdentifier` column to `wcf1_user_option` and creates the
 * `wcf1_user_option_l10n` table that stores the localized title and description
 * of a user option.
 *
 * IMPORTANT ordering constraint for package.xml: This script must run BEFORE
 * the data migration in `acp/update_com.woltlab.wcf_6.3_userOptionL10n.php`.
 */

use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_user_option')
        ->columns([
            VarcharDatabaseTableColumn::create('l10nIdentifier')
                ->length(255),
        ]),
    DatabaseTable::create('wcf1_user_option_l10n')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('optionID'),
            IntDatabaseTableColumn::create('languageID'),
            VarcharDatabaseTableColumn::create('title')
                ->length(255),
            MediumtextDatabaseTableColumn::create('description'),
            TinyintDatabaseTableColumn::create('isPristine')
                ->notNull()
                ->defaultValue(1),
        ])
        ->indices([
            DatabaseTableIndex::create('optionID')
                ->columns(['optionID', 'languageID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['optionID'])
                ->referencedTable('wcf1_user_option')
                ->referencedColumns(['optionID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->onUpdate('NO ACTION'),
        ]),
];
