<?php

/**
 * Drops the reactionTypeID foreign key to later recreate it with the correct name.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_like')
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['reactionTypeID'])
                ->referencedTable('wcf1_reaction_type')
                ->referencedColumns(['reactionTypeID'])
                ->onDelete('CASCADE')
                ->drop(),
        ]),
];
