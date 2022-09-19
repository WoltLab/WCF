<?php

/**
 * Updates the database layout during the update from 5.5 to 6.0.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\column\BigintDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\column\VarbinaryDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_acp_template')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
        ]),
    PartialDatabaseTable::create('wcf1_language_item')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('packageID'),
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
];
