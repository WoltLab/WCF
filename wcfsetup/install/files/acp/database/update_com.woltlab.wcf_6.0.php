<?php

/**
 * Updates the database layout during the update from 5.5 to 6.0.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\DefaultFalseBooleanDatabaseTableColumn;
use wcf\system\database\table\column\EnumDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\SmallintDatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarbinaryDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTablePrimaryIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_acp_template')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    // This needs to be separate, because the FK needs to be adjusted first.
    PartialDatabaseTable::create('wcf1_article')
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['categoryID'])
                ->referencedTable('wcf1_category')
                ->referencedColumns(['categoryID'])
                ->onDelete('CASCADE'),
        ]),
    PartialDatabaseTable::create('wcf1_article')
        ->columns([
            SmallintDatabaseTableColumn::create('attachments')
                ->length(5)
                ->notNull()
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('categoryID'),
        ]),
    PartialDatabaseTable::create('wcf1_captcha_question')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('views')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('correctSubmissions')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('incorrectSubmissions')
                ->defaultValue(0),
        ]),
    PartialDatabaseTable::create('wcf1_language_item')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    PartialDatabaseTable::create('wcf1_language')
        ->columns([
            VarcharDatabaseTableColumn::create('locale')
                ->notNull()
                ->length(50)
                ->defaultValue(''),
        ]),
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
            NotNullInt10DatabaseTableColumn::create('packageID'),
            VarbinaryDatabaseTableColumn::create('sha256')
                ->length(32)
                ->defaultValue(null),
            BigintDatabaseTableColumn::create('lastUpdated')
                ->length(20)
                ->defaultValue(null),
        ]),
    PartialDatabaseTable::create('wcf1_package_installation_plugin')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    PartialDatabaseTable::create('wcf1_package_installation_sql_log')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    PartialDatabaseTable::create('wcf1_page')
        ->columns([
            TinyintDatabaseTableColumn::create('isLandingPage')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_style')
        ->columns([
            EnumDatabaseTableColumn::create('apiVersion')
                ->drop(),
            DefaultFalseBooleanDatabaseTableColumn::create('hasDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_style_variable')
        ->columns([
            MediumtextDatabaseTableColumn::create('defaultValueDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_style_variable_value')
        ->columns([
            MediumtextDatabaseTableColumn::create('variableValueDarkMode'),
        ]),
    PartialDatabaseTable::create('wcf1_user_group_option')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            TinyintDatabaseTableColumn::create('enableGravatar')
                ->drop(),
            VarcharDatabaseTableColumn::create('gravatarFileExtension')
                ->drop(),
        ]),
    PartialDatabaseTable::create('wcf1_package_compatibility')
        ->drop(),
    PartialDatabaseTable::create('wcf1_package_update_compatibility')
        ->drop(),
    PartialDatabaseTable::create('wcf1_package_update_optional')
        ->drop(),
    PartialDatabaseTable::create('wcf1_user_notification_to_user')
        ->drop(),
    PartialDatabaseTable::create('wcf1_cli_history')
        ->drop(),
];
