<?php

/**
 * First step of the updates to the `wcf1_tag_to_object` table.
 *
 * The foreign keys needs to be dropped first because they required the dropped indices.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_tag_to_object')
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['tagID'])
                ->referencedTable('wcf1_tag')
                ->referencedColumns(['tagID'])
                ->onDelete('CASCADE')
                ->drop(),
            DatabaseTableForeignKey::create()
                ->columns(['languageID'])
                ->referencedTable('wcf1_language')
                ->referencedColumns(['languageID'])
                ->onDelete('CASCADE')
                ->drop(),
            DatabaseTableForeignKey::create()
                ->columns(['objectTypeID'])
                ->referencedTable('wcf1_object_type')
                ->referencedColumns(['objectTypeID'])
                ->onDelete('CASCADE')
                ->drop(),
        ])
        ->indices([
            DatabaseTableIndex::create()
                ->columns(['objectTypeID', 'languageID', 'objectID', 'tagID'])
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->drop(),
            DatabaseTableIndex::create()
                ->columns(['tagID', 'objectTypeID'])
                ->drop(),
        ]),
];
