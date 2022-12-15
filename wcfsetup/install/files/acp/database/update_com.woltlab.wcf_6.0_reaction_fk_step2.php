<?php

/**
 * Recreates the reactionTypeID foreign key.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
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
                ->onDelete('CASCADE'),
        ]),
];
