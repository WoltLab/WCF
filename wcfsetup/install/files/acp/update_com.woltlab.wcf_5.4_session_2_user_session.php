<?php

use wcf\system\database\table\column\CharDatabaseTableColumn;
use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumblobDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\WCF;

/**
 * Creates the user_session table.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

$tables = [
    DatabaseTable::create('wcf1_user_session')
        ->columns([
            CharDatabaseTableColumn::create('sessionID')
                ->length(40)
                ->notNull(),
            IntDatabaseTableColumn::create('userID')
                ->length(10),
            NotNullVarchar255DatabaseTableColumn::create('userAgent')
                ->defaultValue(''),
            VarcharDatabaseTableColumn::create('ipAddress')
                ->length(39)
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('lastActivityTime')
                ->defaultValue(0),
            MediumblobDatabaseTableColumn::create('sessionVariables'),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->type(DatabaseTableIndex::PRIMARY_TYPE)
                ->columns(['sessionID']),
            DatabaseTableIndex::create()
                ->columns(['userID']),
            DatabaseTableIndex::create()
                ->columns(['lastActivityTime']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('CASCADE'),
        ]),
];

(new DatabaseTableChangeProcessor(
/** @var ScriptPackageInstallationPlugin $this */
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
)
)->process();
