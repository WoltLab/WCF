<?php

/**
 * Updates the database layout during the update from 6.2 to 6.3.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_style')
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['templateGroupID'])
                ->referencedTable('wcf1_template_group')
                ->referencedColumns(['templateGroupID'])
                ->onDelete('SET NULL')
                ->onUpdate('NO ACTION'),
        ]),
];
